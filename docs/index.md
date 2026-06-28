# Index

Index is an abstract base class. Extend it to define an index, register an ES client, then query.

## Configuration

```php
use ElasticKit\Index\Index;

// create the official client
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])
    ->build();

// register as the default connection
Index::setClient($client);

// multiple connections
Index::setClient($mainClient, 'main');
Index::setClient($logClient, 'logs');
```

Define an index:

```php
class ProductIndex extends Index
{
    protected string $name = 'products';       // index name (required)
    protected array $mappings = [             // index mappings
        'properties' => [
            'title'  => ['type' => 'text'],
            'price'  => ['type' => 'float'],
            'status' => ['type' => 'keyword'],
        ],
    ];
    protected array $settings = [             // index settings
        'number_of_shards' => 1,
    ];
    protected string $connection = 'main';     // connection name (default 'default')

    public function rebuildName(): string // the real index name after rebuild (override to customize)
    {
        return $this->name . '_' . date('Ymd_His');
    }
}
```

## Search

`query()` returns a new Search instance. Chain DSL methods, then execute:

```php
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->sort('price', 'asc')
    ->size(20)
    ->get();

$results->total();        // hit count (null unless the index sets $trackTotalHits = true)
$results->docs();         // array of _source
$results->hits();         // full hit array
$results->aggregations(); // aggregation results
```

```php
// return only the first (internally sets size=1)
$doc = ProductIndex::query()->match('title', 'test')->first();

// don't fetch docs, just count
$total = ProductIndex::query()->term('status', 'published')->count();

// aggregation shortcuts (internally set size=0)
$avg = ProductIndex::query()->avg('price');
$max = ProductIndex::query()->max('price');
$min = ProductIndex::query()->min('price');
$sum = ProductIndex::query()->sum('price');
```

## Pagination

```php
use ElasticKit\Index\Support\Pagination;

// manual pagination
$results = ProductIndex::query()->paginate($page, $perPage);

// auto-resolve from the request
Pagination::setPageResolver(function () {
    return [request('page', 1), request('per_page', 20)];
});
$results = ProductIndex::query()->paginate();

// wire up a framework paginator
Pagination::setPaginatorResolver(function ($results, $page, $perPage) {
    return new LengthAwarePaginator($results->docs(), $results->total(), $perPage, $page);
});
$results->toPaginator();
```

> **Total tracking is opt-in.** `Index` defaults `$trackTotalHits = false`, so Elasticsearch omits the hit total: `total()`/`lastPage()` return `null` and `toPaginator()` throws. For length-aware pagination, enable it on the index — `true` counts every hit, an int caps the count (e.g. 5000):
>
> ```php
> class ProductIndex extends Index
> {
>     protected int|bool $trackTotalHits = true;
> }
> ```
>
> Otherwise use total-less pagination: `hasMorePages()` (full-page heuristic) or `chunk()` / `cursor()`. Deep pagination past 10,000 (`from + size > max_result_window`) is rejected by Elasticsearch — use `chunk()` (scroll) there.

## Scroll

For large datasets, use scroll to fetch in batches:

```php
// first batch (default size=1000)
$results = ProductIndex::query()->size(500)->scroll();
$total = $results->total();
$scrollId = $results->scrollId();

// keep fetching
while (count($results->docs()) > 0) {
    // process $results->docs()...
    $results = ProductIndex::query()->scroll($scrollId);
    $scrollId = $results->scrollId();
}

// clear when done
ProductIndex::query()->clear($results);
```

## Chunk / Cursor

Wraps scroll into a PHP generator; the scroll is cleared automatically.

**chunk** iterates by batch, yielding a Results each time (with docs/hits/total etc.):

```php
foreach (ProductIndex::query()->chunk() as $results) {
    foreach ($results->docs() as $doc) {
        // process
    }
}
```

**cursor** iterates per hit, yielding one full hit each time (_id/_score/_source):

```php
foreach (ProductIndex::query()->cursor() as $hit) {
    $doc = $hit['_source'];
    $id  = $hit['_id'];
}
```

## Document CRUD

```php
$doc = ProductIndex::doc(1);

$doc->create(['title' => 'New Product', 'price' => 29.99]);
$doc->source();   // get the _source array
$doc->update(['price' => 39.99]);

// update with conflict retry
$doc->retryOnConflict(3)->update(['price' => 39.99]);

$doc->delete();
```

`update()` does not use upsert semantics by default — it throws if the document doesn't exist. Pass `true` to enable upsert:

```php
$doc->update(['price' => 39.99]);           // throws if the document doesn't exist
$doc->update(['price' => 39.99], true);     // creates it if it doesn't exist
```

## Bulk operations

Bulk is a buffer: `index()/create()/update()/delete()` only enqueue; **`flush()` is what sends**.

```php
use ElasticKit\Index\Bulk;

$bulk = new Bulk(new ProductIndex());
$bulk->index(1, ['title' => 'Product A']);
$bulk->index(2, ['title' => 'Product B']);
$bulk->delete(3);
$bulk->flush(); // send and clear the buffer
```

`batchSize(N)` enables **auto-flush**: when the buffer reaches N it sends automatically (default 0 = off, pure buffering). Use it for large imports to avoid piling up memory; after the loop you **still need `flush()` to send the tail**:

```php
$bulk = (new Bulk(new ProductIndex()))->batchSize(500);
foreach ($docs as $id => $doc) {
    $bulk->index($id, $doc);   // auto-flushes at 500
}
$bulk->flush();                 // the tail (< 500)
```

### Error handling

`flush()` throws a `RuntimeException` by default when the response contains errors. Use `onError()` to customize — the callback receives three raw materials and decides what to do:

- `$response` — the raw ES response (`items[]` carry per-item status/error)
- `$body` — the full original batch (successes included, native ES format)
- `$newbulk` — a fresh Bulk bound to the same index + target, for re-sending failures

Inside the callback **don't throw (return) → treated as handled, this batch is cleared and we continue; throw → abort, this batch is preserved** for the caller.

```php
// no onError → throws RuntimeException on error
$bulk->flush();

// with onError → handle failures yourself (you can re-send)
$bulk->onError(function (array $response, array $body, Bulk $newbulk) {
    // items[k] ↔ the k-th action; pick out the failures and re-send (simple alignment for a pure-index batch)
    foreach ($response['items'] as $i => $item) {
        $meta = $item[array_key_first($item)];
        if (($meta['status'] ?? 200) >= 400) {
            $newbulk->index($meta['_id'], $body[$i * 2 + 1]);
        }
    }
    $newbulk->flush();
})->flush();
```

> Errors from a `batchSize` auto-flush also go through `onError`.

## Zero-downtime rebuild

Create a new index → import data → swap the alias.

`$name` is always the application-facing name. The application never needs to change which name it uses — all CRUD, search, and bulk operations always target `$name`. After a rebuild, `$name` becomes an alias pointing at the new index generated by `rebuildName()`.

```php
use ElasticKit\Index\Rebuild;

$rebuild = new Rebuild(new ProductIndex());

// rebuild: returns the new and old index names
$result = $rebuild->batchSize(500)->run();
// $result = ['newIndex' => 'products_20250601_120000', 'oldIndex' => 'products_20250531_090000']

// once confirmed, clean up the old index
$rebuild->clean($result['oldIndex']);

// or roll back if something went wrong
$rebuild->rollback($result['oldIndex']);
```

### How it works

`run()` auto-detects the current state:

1. **$name is already an alias** (subsequent rebuilds): atomic alias swap, zero downtime
2. **$name is a real index**: throws a `RuntimeException`; you must first delete it manually or convert to alias mode
3. **$name doesn't exist**: creates a new index and sets up the alias

After a rebuild `$name` becomes an alias pointing at the new index generated by `rebuildName()`. The old index is kept; you decide whether to `clean()` or `rollback()`.

### Custom naming

Override `rebuildName()` to customize the new index name:

```php
class ProductIndex extends Index
{
    public function rebuildName(): string
    {
        return $this->name . '_v' . time();
    }
}
```

### Data source

Override `source()` in an Index subclass to feed the rebuild. The base class throws if not overridden:

```php
class ProductIndex extends Index
{
    public function source(array $context = []): iterable
    {
        foreach (Product::all() as $product) {
            yield $product->id => $product->toArray();
        }
    }
}
```

You can also pass a custom data source at call time:

```php
$rebuild->source(function () {
    yield 1 => ['title' => 'test'];
})->run();
```

`run()` accepts an optional `$context` parameter, forwarded to `source()`:

```php
$rebuild->run(['after' => '2024-01-01']);
```

### Error handling

Rebuild uses Bulk internally for the import; `onError()` works the same as in [Bulk operations > Error handling](#error-handling):

```php
$rebuild->onError(function (array $response, array $body, Bulk $newbulk) {
    Log::warning("Rebuild import error", $response);
    // to re-send failures use $body + $newbulk, see "Bulk operations > Error handling"
})->run();
```

> During a rebuild the DB keeps changing; the new index is a snapshot of the start moment — after the rebuild, top it up incrementally via `updated_at`.
>
> After adding fields, running sort/agg/collapse on fields that don't have a mapping yet will error; evaluate whether to `putMapping()` before deploying. Modifying/removing fields requires a staged deployment.

## Reference

### Manager

A thin proxy over the ES indices API. `new Manager($index)`; adds no methods to Index:

```php
use ElasticKit\Index\Manager;

$manager = new Manager(new ProductIndex());
```

### Events

```php
use ElasticKit\Index\Support\Event;
use ElasticKit\Index\Support\EventDispatcher;

EventDispatcher::listen('search.query.after', function (Event $e) {
    Log::info("{$e->name} on {$e->index}", ['duration' => $e->duration]);
});

// wildcards
EventDispatcher::listen('search.*', function (Event $e) { /* ... */ });
EventDispatcher::listen('*', function (Event $e) { /* ... */ });
```

All events carry `$name` and `$index`.

### Custom client

Use the `ClientBuilder` to configure the client (hosts, SSL, logging, etc.):

```php
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['https://localhost:9200'])
    ->setLogger($logger)
    ->build();

Index::setClient($client);
```

## Security

The following methods accept raw ES parameters and **must never** receive user input directly:

- `script()`, `scriptScore()`, `scriptFields()`, `runtimeMappings()` — Painless script execution
- `Bulk::target()` — target index override
- `sort()` with the `_script` type — script execution via sorting
- `postFilter()` — raw query pass-through

Always validate and filter user input before passing it to DSL methods.

## Cheat sheet

### Manager methods

| Method | Description |
|------|------|
| `create()` | Create the index (with mappings and settings) |
| `delete()` | Delete the index |
| `exists()` | Check whether the index exists |
| `get()` | Get index info |
| `open()` | Open the index |
| `close()` | Close the index |
| `putMapping()` | Update the index mappings (uses the Index definition) |
| `getMapping()` | Get the index mappings |
| `putSettings($settings)` | Update the index settings |
| `getSettings()` | Get the index settings |
| `refresh()` | Refresh the index |
| `forceMerge()` | Force-merge index segments |
| `addAlias($alias)` | Add an alias |
| `removeAlias($alias)` | Remove an alias |
| `swapAlias($alias, $target)` | Swap where an alias points |
| `getAliases()` | Get the index's aliases |

### Event list

All events carry `$name` and `$index`. `$action` is the called method name: `get`, `first`, `count`, `scroll`, or `paginate`.

| Event | Properties |
|------|------|
| `search.query.before` | `$dsl`, `$action` |
| `search.query.after` | `$dsl`, `$response`, `$duration`, `$action` |
| `search.scroll.before` | `$action`, `$scrollId` |
| `search.scroll.after` | `$action`, `$scrollId`, `$response`, `$duration` |
| `bulk.flush.before` | `$actions` |
| `bulk.flush.after` | `$actions`, `$response`, `$duration` |
| `manager.create.before` | |
| `manager.create.after` | `$response` |
| `manager.delete.before` | |
| `manager.delete.after` | `$response` |
| `manager.swap_alias.before` | |
| `manager.swap_alias.after` | `$response` |
| `rebuild.run.before` | |
| `rebuild.run.after` | `$newIndex`, `$oldIndex` |

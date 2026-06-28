# ElasticKit

> [中文](README.zh.md) | English

[![Latest Version](https://img.shields.io/packagist/v/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![Total Downloads](https://img.shields.io/packagist/dt/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![License](https://img.shields.io/packagist/l/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)

A PHP Elasticsearch DSL query builder covering queries, aggregations, CRUD, bulk writes, and zero-downtime rebuilds.

## Installation

```
composer require ykan/elastickit:^8
```

> Requires PHP 8.1+ and Elasticsearch 8.x. The `elasticsearch-php` dependency is installed automatically.

## Quick Start

```php
use ElasticKit\Index\Index;

// 1. Register the client
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])->build();
Index::setClient($client);

// 2. Define an index
class ProductIndex extends Index
{
    protected string $name = 'products';
    protected array $mappings = [
        'properties' => [
            'title'  => ['type' => 'text'],
            'price'  => ['type' => 'float'],
            'status' => ['type' => 'keyword'],
        ],
    ];
}

// 3. Search
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->get();

$hits = $results->docs();   // [['title' => '...'], ...]
$total = $results->total(); // null unless $trackTotalHits = true (see Pagination & cursor)
```

## DSL Examples

<details>
<summary>Expand</summary>

### Polymorphic parameters

The same method accepts four forms — string, array, closure, object:

```php
$q->term('status', 'published');                                     // string
$q->term(['status' => 'published']);                                 // array
$q->term(fn ($t) => $t->field('status')->value('published'));        // closure
$q->term(Term::create('status', 'published'));                       // object
```

### OOP style

Each query type is a dedicated Node class supporting chaining:

```php
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\Compound\Boolean;

$bool = Boolean::create()
    ->must(Match_::create('title', 'elasticsearch'))
    ->filter(Term::create('status', 'published')->boost(1.5));

// incremental build
if ($filterByPrice) {
    $bool->filter(Range::create('price', [10, 100]));
}

$query = Query::create($bool);

$query->toArray();  // ['query' => ['bool' => [...]]]
$query->toJson();   // '{"query":{"bool":{...}}}'
```

### Compound query

```php
$results = ProductIndex::query()
    ->bool([
        'must'   => fn ($q) => $q->match('title', 'elasticsearch'),
        'filter' => fn ($q) => $q
            ->range('price', [10, 100])
            ->when($status, fn ($q) => $q->term('status', $status))  // conditional filter
            ->term('status', 'published'),
    ])
    ->highlight('title')
    ->sort('price', 'asc')
    ->size(20)
    ->get();
```

```json
{
  "query": {
    "bool": {
      "must": [{ "match": { "title": "elasticsearch" } }],
      "filter": [
        { "range": { "price": { "gte": 10, "lte": 100 } } },
        { "term": { "status": "published" } }
      ]
    }
  },
  "highlight": { "fields": { "title": {} } },
  "sort": [{ "price": "asc" }],
  "size": 20
}
```

### Clause appending (ClausesSupport)

The clauses of a `bool` query (must / should / filter / must_not) **append**, and accept the same four input forms as leaf queries:

```php
// all four forms are equivalent, each produces one must clause
$q->bool(fn ($b) => $b->must(fn ($q) => $q->term('status', 'published')));
$q->bool(['must' => fn ($q) => $q->term('status', 'published')]);
$q->bool('must', fn ($q) => $q->term('status', 'published'));

// clauses accumulate (multiple calls and list form both append)
$q->bool(fn ($b) => $b->must(...)->must(...));   // must: [q1, q2]
$q->bool(['must' => [$q1, $q2]]);                // same

// contrast: minimum_should_match is a single-value property; later calls overwrite instead of append
$q->bool(fn ($b) => $b->minimumShouldMatch(1)->minimumShouldMatch(3)); // 3
```

> `dis_max`, `span_or`, `span_near` and other array-clause containers behave the same way (queries / clauses append).

### Aggregations

```php
$results = ProductIndex::query()
    ->matchAll()
    ->aggs('status_counts', fn ($agg) => $agg->terms('status'))
    ->aggs('price_stats', fn ($agg) => $agg->stats('price'))
    ->size(0)
    ->get();

$aggs = $results->aggregations();
```

### Nested query

```php
$results = ProductIndex::query()
    ->nested('comments', fn ($q) => $q->match('comments.body', 'great'))
    ->get();
```

### Raw DSL pass-through

```php
// supports raw arrays with nested closures; query/aggs/parameters can be passed all at once
$query = Query::create([
    'query' => [
        'bool' => [
            'must'   => fn ($q) => $q->match('title', 'elasticsearch'),
            'filter' => fn ($q) => $q->term('status', 'published'),
        ],
    ],
    'size' => 20,
    'sort' => [['price' => 'asc']],
]);
```

</details>

## Index Examples

<details>
<summary>Expand</summary>

### Pagination & cursor

```php
// pagination
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->paginate($page, $perPage);

$results->lastPage();
$results->items();
$results->toPaginator();  // convert to a framework paginator (requires registering a Paginator Resolver)

// batch iteration (large exports / batch processing; yields a Results per batch)
foreach (ProductIndex::query()->chunk() as $results) {
    foreach ($results->docs() as $doc) {
        // ...
    }
}

// per-hit iteration (exports / per-row processing; yields one hit: _id/_score/_source)
foreach (ProductIndex::query()->cursor() as $hit) {
    $doc = $hit['_source'];
    // ...
}
```

> **Pagination needs totals, which are opt-in.** `Index` defaults `$trackTotalHits = false`, so `total()`/`lastPage()` return `null` and `toPaginator()` throws. Set `protected int|bool $trackTotalHits = true;` (or a count cap) on the index for page-count pagination, or use `hasMorePages()` / `chunk()` / `cursor()` for total-less iteration.

### Document CRUD

```php
ProductIndex::doc(1)->save(['title' => 'Hello', 'price' => 99.9]);

$doc = ProductIndex::doc(1);
$doc->source();  // ['title' => 'Hello', 'price' => 99.9]

$doc->update(['price' => 89.9]);
$doc->delete();
```

### Bulk operations

```php
use ElasticKit\Index\Bulk;

$bulk = new Bulk(new ProductIndex());

$bulk->batchSize(500)
    ->index(1, ['title' => 'A', 'price' => 10])
    ->index(2, ['title' => 'B', 'price' => 20])
    ->update(3, ['price' => 15])
    ->delete(4)
    ->flush();
```

### Index management

```php
use ElasticKit\Index\Manager;

$manager = new Manager(new ProductIndex());

$manager->create();       // create the index
$manager->exists();       // bool
$manager->putMapping();   // update the mapping
$manager->delete();       // delete the index
```

### Zero-downtime rebuild

```php
use ElasticKit\Index\Rebuild;

// 1. Define the data source in an Index subclass
class ProductIndex extends Index
{
    public function source(array $context = []): iterable
    {
        foreach (Db::table('products')->cursor() as $row) {
            yield $row['id'] => $row;
        }
    }
}

// 2. Run the rebuild (creates a new index -> imports -> swaps the alias)
$result = (new Rebuild(new ProductIndex()))
    ->batchSize(500)
    ->run();

// $result = ['newIndex' => 'products_20260607_120000', 'oldIndex' => 'products_20260601_090000']

// 3. Clean up old indices or roll back
(new Rebuild(new ProductIndex()))->clean($result['oldIndex']);
(new Rebuild(new ProductIndex()))->rollback($result['oldIndex']);
```

### Event listening

```php
use ElasticKit\Index\Support\Event;
use ElasticKit\Index\Support\EventDispatcher;

EventDispatcher::listen('search.query.after', function (Event $e) {
    Log::info("Search on {$e->index}", [
        'dsl' => $e->dsl,
        'duration' => $e->duration,
    ]);
});

EventDispatcher::listen('search.*', function (Event $e) {
    Log::debug($e->name);
});
```

</details>

## Long-lived processes

`Index::setClient()`, `ClientManager`, `EventDispatcher`, and `Pagination` hold **static state**. In a long-lived worker (Swoole, RoadRunner, Laravel Octane) this state persists across requests, so a worker leaks the registered client, event listeners, and pagination resolvers between requests.

Reset them between requests — e.g. in a request-terminated hook:

```php
use ElasticKit\Index\Support\ClientManager;
use ElasticKit\Index\Support\EventDispatcher;
use ElasticKit\Index\Support\Pagination;

ClientManager::reset();
EventDispatcher::reset();
Pagination::reset();
```

PHP-FPM forks a worker per request, so this only affects persistent workers.

## Documentation

- [Guide](docs/guide.md) — an e-commerce order scenario, the full flow from install to production
- [Index docs](docs/index.md) — search, CRUD, bulk operations, zero-downtime rebuild, events
- [Changelog](CHANGELOG.md)
- [Elasticsearch official docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html) — query types and parameter reference

## AI-assisted development

This project is developed with AI assistance; core paths and tests are human-reviewed.

## License

MIT

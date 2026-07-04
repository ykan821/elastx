# ElasticKit

> [中文](README.zh.md) | English

[![Latest Version](https://img.shields.io/packagist/v/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![Total Downloads](https://img.shields.io/packagist/dt/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![Tests](https://github.com/ykan821/ElasticKit/actions/workflows/ci.yml/badge.svg)](https://github.com/ykan821/ElasticKit/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/packagist/php-v/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![License](https://img.shields.io/packagist/l/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)

A PHP Elasticsearch DSL query builder covering queries, aggregations, CRUD, bulk writes, and zero-downtime rebuilds.

## Integrations

- **[ElasticKit Laravel](https://github.com/ykan821/ElasticKitLaravel)** — Laravel integration
  (native pagination, artisan rebuild). `composer require ykan/elastickit-laravel`.

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

ElasticKit's DSL stays close to the native ES API to minimize cognitive load — if you know ES DSL, the transfer is smooth. Every query type is a dedicated `Node` class whose method names mirror ES parameters.

<details>
<summary>Expand</summary>

### Compound query

```php
$results = ProductIndex::query()
    ->bool([
        'must'   => fn ($q) => $q->match('title', 'elasticsearch'),
        'filter' => fn ($q) => $q
            ->range('price', [10, 100])
            ->when($status, fn ($q) => $q->term('status', $status)),  // conditional filter
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

### OOP style

Build each clause separately, then combine them into a query:

```php
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Term;

// Build the clauses
$status = Term::create('status', 'published')->boost(1.5);
$title  = Match_::create('title', 'elasticsearch');

// Combine into a bool query
$bool = Boolean::create()->must($title)->filter($status);
if ($filterByPrice) {
    $bool->filter(Range::create('price', [10, 100]));
}

// Execute
$results = ProductIndex::query()->bool($bool)->size(20)->get();
```

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

### kNN search

```php
$results = ProductIndex::query()
    ->knn(fn ($k) => $k
        ->field('embedding')
        ->queryVector([0.12, 0.45, 0.78, /* ... */])
        ->numCandidates(100))
    ->size(10)
    ->get();
```

### Raw array

```php
$query = Query::create([
    'query' => [
        'bool' => [
            'must'   => fn ($q) => $q->match('title', 'elasticsearch'),  // array may nest closures
            'filter' => fn ($q) => $q->term('status', 'published'),
        ],
    ],
    'size' => 20,
    'sort' => [['price' => 'asc']],
]);
```

### Clause appending (ClausesSupport)

The clauses of a `bool` query (must / should / filter / must_not) **append** — repeated calls accumulate, they don't overwrite:

```php
$q->bool(fn ($b) => $b->must($q1));   // must: [q1]
$q->bool(fn ($b) => $b->must($q2));   // must: [q1, q2]
```

> `dis_max`, `span_or`, `span_near` and other array-clause containers behave the same way.

### Flexible arguments

The same method accepts multiple input forms — use whichever suits:

```php
$q->term('status', 'published');                                     // string
$q->term(['status' => 'published']);                                 // array
$q->term(fn ($t) => $t->field('status')->value('published'));        // closure
$q->term(Term::create('status', 'published'));                       // object
```

</details>

## Index Examples

Around the `Index` base class, dedicated classes cover routine index operations — pagination, CRUD, bulk writes, index management, zero-downtime rebuilds, and event hooks.

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

## Documentation

- [Guide](docs/guide.md) — an e-commerce order scenario, the full flow from install to production
- [Index docs](docs/index.md) — search, CRUD, bulk operations, zero-downtime rebuild, events
- [Changelog](CHANGELOG.md)
- [Elasticsearch official docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html) — query types and parameter reference

## AI-assisted development

This project is developed with AI assistance; core paths and tests are human-reviewed.

## License

MIT

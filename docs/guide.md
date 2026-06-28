# ElasticKit Practical Guide

Using an e-commerce order module as an example, this guide walks through the complete workflow with ElasticKit.

## Phase 1: Installation & Configuration

The operations team has a requirement: an order search page that searches by order number, filters by status and date, plus a sales analytics dashboard.

Install:

```
composer require ykan/elastickit:^8
```

Register the ES client:

```php
// app/Providers/AppServiceProvider.php

use ElasticKit\Index\Index;
use Elastic\Elasticsearch\ClientBuilder;

public function boot(): void
{
    Index::setClient(
        ClientBuilder::create()
            ->setHosts(['http://localhost:9200'])
            ->build()
    );
}
```

## Phase 2: Design the index

Order data is spread across the orders, users, and merchants tables. ES doesn't support joins, so **assemble related data into a single document at write time**.

```php
use ElasticKit\Index\Index;
use Illuminate\Support\Facades\Db;

class OrderIndex extends Index
{
    protected string $name = 'orders';

    protected array $mappings = [
        'properties' => [
            'order_no'      => ['type' => 'keyword'],       // exact match
            'status'        => ['type' => 'keyword'],       // pending/paid/shipped/completed
            'user_name'     => ['type' => 'keyword'],       // joined from users
            'merchant_name' => ['type' => 'keyword'],       // joined from merchants
            'total_amount'  => ['type' => 'float'],
            'paid_at'       => ['type' => 'date'],
            'created_at'    => ['type' => 'date'],
        ],
    ];

    public function source(array $context = []): iterable
    {
        // join users + merchants, assemble every field the search needs
        $query = Db::table('orders')
            ->select([
                'orders.*',
                'users.name as user_name',
                'merchants.name as merchant_name',
            ])
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('merchants', 'orders.merchant_id', '=', 'merchants.id');

        // for incremental sync, query only the given IDs
        if (isset($context['ids'])) {
            $query->whereIn('orders.id', $context['ids']);
        }

        // yield [docId => docData]; Rebuild writes them in bulk internally
        foreach ($query->cursor() as $order) {
            yield $order['id'] => [
                'order_no'      => $order['order_no'],
                'status'        => $order['status'],
                'user_name'     => $order['user_name'],
                'merchant_name' => $order['merchant_name'],
                'total_amount'  => (float) $order['total_amount'],
                'paid_at'       => $order['paid_at'],
                'created_at'    => $order['created_at'],
            ];
        }
    }
}
```

> `user_name` and `merchant_name` are assembled from related tables at write time, so no join is needed at query time. Pass `['ids' => [...]]` for incremental queries.

## Phase 3: Initial import

With the index designed, import the existing orders into ES.

```php
use ElasticKit\Index\Rebuild;

$result = (new Rebuild(new OrderIndex()))
    ->batchSize(500)
    ->run();

// $result = ['newIndex' => 'orders_20260607_120000', 'oldIndex' => null]
```

Rebuild does it all automatically: creates a new index (`orders_20260607_120000`) -> reads from `source()` -> bulk-writes via Bulk -> points the `orders` alias at the new index. On the first import `oldIndex` is null.

## Phase 4: Search & filtering

Operations wants an order search page with many, dynamic conditions. Encapsulate the condition building inside the Index; the controller just calls it.

Add a search method to OrderIndex:

```php
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Wildcard;
use ElasticKit\DSL\Queries\Compound\Boolean;

class OrderIndex extends Index
{
    // ... mappings and source() as in Phase 2

    public static function searchOrders(array $filters)
    {
        $bool = Boolean::create();

        // exact filters (no scoring needed -> put in filter)
        if (!empty($filters['status'])) {
            $bool->filter(Term::create('status', $filters['status']));
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $bool->filter(Range::create('created_at', [$filters['start_date'], $filters['end_date']]));
        }

        // keyword search (OR -> put in should)
        if (!empty($filters['keyword'])) {
            $bool->should(Wildcard::create('order_no', "*{$filters['keyword']}*"));
            $bool->should(Wildcard::create('merchant_name', "*{$filters['keyword']}*"));
        }

        return static::query(Query::create($bool));
    }
}
```

Controller:

```php
// app/Http/Controllers/OrderController.php
public function index(Request $request)
{
    $results = OrderIndex::searchOrders($request->all())
        ->sort('created_at', 'desc')
        ->paginate();

    return $results->toPaginator();
}
```

> `toPaginator()` needs a hit total. Set `protected int|bool $trackTotalHits = true;` on `OrderIndex` — `Index` defaults to `false`, which omits the total and makes `toPaginator()` throw.

> Conditions are checked one by one with `if`; a clause is added only when a value is present. `should()` implements OR search. For deep pagination use `chunk()` (batches) or `cursor()` (per hit) instead of `paginate()`.

Operations also wants to export the filtered results to Excel. ES defaults to `max_result_window = 10000`, so `from/size` can't reach later data; iterate with `cursor()` (scroll-based):

```php
public function export(array $filters)
{
    $search = static::searchOrders($filters)->sort('created_at', 'desc');

    foreach ($search->chunk() as $results) {
        foreach ($results->docs() as $doc) {
            // write to Excel
        }
    }
}
```

## Phase 5: Aggregation statistics

The admin dashboard needs monthly sales totals, grouped by merchant. The filter conditions reuse `searchOrders()`.

```php
public function statistics(array $filters)
{
    // reuse searchOrders' filters; size(0) returns no docs, only aggregations
    $search = static::searchOrders($filters)->size(0);

    // monthly sales totals
    $search->aggs('monthly', function ($agg) {
        $agg->dateHistogram([
            'field'             => 'created_at',
            'calendar_interval' => 'month',
            'format'            => 'yyyy-MM',
            'time_zone'         => 'Asia/Shanghai',
        ]);
        $agg->aggs('revenue', fn ($a) => $a->sum('total_amount'));
    });

    // group + total by merchant
    $search->aggs('by_merchant', function ($agg) {
        $agg->terms('merchant_name');
        $agg->aggs('revenue', fn ($a) => $a->sum('total_amount'));
    });

    $results = $search->get();
    return $results->aggregations();
}
```

## Phase 6: Incremental sync

Order status changes, merchant renames — ES must follow. Triggers can be ORM events, message queues, binlog listeners, etc.; the flow is always the same: **collect the document ID list -> push to a queue for async processing**.

Push to a queue (don't update ES directly):

```php
// in OrderIndex, push to the queue
public static function syncOrders(array $ids)
{
    foreach (array_chunk($ids, 100) as $chunk) {
        Queue::push(SyncEsJob::class, ['class' => static::class, 'ids' => $chunk]);
    }
}

// triggered via binlog listener, ORM events, etc.; once you have doc_ids, update async
OrderIndex::syncOrders($orderIds);
```

A generic SyncEsJob, reused by every index:

```php
use ElasticKit\Index\Bulk;

class SyncEsJob
{
    public function fire($job, $data)
    {
        $class = $data['class'];
        $index = new $class();
        $bulk = (new Bulk($index))->batchSize(500);

        foreach ($index->source(['ids' => $data['ids']]) as $id => $doc) {
            $bulk->index($id, $doc);
        }

        $bulk->flush();
        $job->delete();
    }
}
```

## Phase 7: Schema evolution

After launch the product adds a field, e.g. "remark". Add it to OrderIndex mappings and source:

```php
// add the field to mappings
'remark' => ['type' => 'text'],

// add the field to source's yield
'remark' => $order['remark'],
```

Then Rebuild:

```php
$rebuildStartTime = now();

$rebuild = new Rebuild(new OrderIndex());
$result = $rebuild->batchSize(500)->run();
$rebuild->clean($result['oldIndex']);

// During Rebuild the DB keeps changing; the new index is a snapshot of the start moment,
// so top it up incrementally by updated_at
$orderIds = Db::table('orders')
    ->where('updated_at', '>=', $rebuildStartTime)
    ->pluck('id');

OrderIndex::syncOrders($orderIds);
```

`run()` does it all: creates a new index -> imports -> swaps the alias, zero downtime.

---

→ [Index docs](index.md) — full API reference.

# ElasticKit 实践指南

以电商订单模块为例，演示 ElasticKit 的完整使用流程。

## 阶段 1：安装与配置

运营提了需求：订单要有查询页面，能搜订单号、按状态和日期筛选，还要一个销售统计看板。

安装：

```
composer require ykan/elastickit:^8
```

注册 ES Client：

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

## 阶段 2：设计索引

订单数据分散在订单表、用户表、商家表。ES 不支持 join，**写入时把关联数据组装到一条文档里**。

```php
use ElasticKit\Index\Index;
use Illuminate\Support\Facades\Db;

class OrderIndex extends Index
{
    protected $name = 'orders';

    protected $mappings = [
        'properties' => [
            'order_no'      => ['type' => 'keyword'],       // 精确匹配
            'status'        => ['type' => 'keyword'],       // pending/paid/shipped/completed
            'user_name'     => ['type' => 'keyword'],       // 关联用户表
            'merchant_name' => ['type' => 'keyword'],       // 关联商家表
            'total_amount'  => ['type' => 'float'],
            'paid_at'       => ['type' => 'date'],
            'created_at'    => ['type' => 'date'],
        ],
    ];

    public function source(array $context = []): iterable
    {
        // 关联用户、商家表，组装查询所需的全部字段
        $query = Db::table('orders')
            ->select([
                'orders.*',
                'users.name as user_name',
                'merchants.name as merchant_name',
            ])
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('merchants', 'orders.merchant_id', '=', 'merchants.id');

        // 增量同步时只查指定 ID
        if (isset($context['ids'])) {
            $query->whereIn('orders.id', $context['ids']);
        }

        // yield 返回 [文档ID => 文档数据]，Rebuild 内部用 Bulk 批量写入
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

> `user_name`、`merchant_name` 写入时从关联表组装，查询时不再需要 join。传 `['ids' => [...]]` 支持增量查询。

## 阶段 3：首次导入

索引设计好了，把现有订单导入 ES。

```php
use ElasticKit\Index\Rebuild;

$result = (new Rebuild(new OrderIndex()))
    ->batchSize(500)
    ->run();

// $result = ['newIndex' => 'orders_20260607_120000', 'oldIndex' => null]
```

Rebuild 自动完成：创建新索引（`orders_20260607_120000`）→ 从 `source()` 取数据 → Bulk 批量写入 → 将 `orders` 别名指向新索引。首次导入时 `oldIndex` 为 null。

## 阶段 4：搜索与筛选

运营要一个订单查询页面，条件多且动态。把条件构建封装到 Index 里，控制器只管调用。

在 OrderIndex 中加一个搜索方法：

```php
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Wildcard;
use ElasticKit\DSL\Queries\Compound\Boolean;

class OrderIndex extends Index
{
    // ... mappings 和 source() 同阶段 2

    public static function searchOrders(array $filters)
    {
        $bool = Boolean::create();

        // 精确筛选（不需要评分，放 filter）
        if (!empty($filters['status'])) {
            $bool->filter(Term::create('status', $filters['status']));
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $bool->filter(Range::create('created_at', [$filters['start_date'], $filters['end_date']]));
        }

        // 关键词搜索（OR，放 should）
        if (!empty($filters['keyword'])) {
            $bool->should(Wildcard::create('order_no', "*{$filters['keyword']}*"));
            $bool->should(Wildcard::create('merchant_name', "*{$filters['keyword']}*"));
        }

        return static::query(Query::create($bool));
    }
}
```

控制器调用：

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

> 条件用 `if` 逐个判断，只有传了值才加查询。`should()` 实现 OR 搜索。深分页场景用 `cursor()` 替代 `paginate()`。

运营还要导出筛选结果到 Excel。ES 默认 `max_result_window = 10000`，`from/size` 翻不到后面的数据，用 `cursor()` 基于 scroll 遍历：

```php
public function export(array $filters)
{
    $search = static::searchOrders($filters)->sort('created_at', 'desc');

    foreach ($search->cursor() as $batch) {
        foreach ($batch->docs() as $doc) {
            // 写入 Excel
        }
    }
}
```

## 阶段 5：聚合统计

管理看板需要按月统计销售额，按商家分组汇总。筛选条件和列表页共用 `searchOrders()`。

```php
public function statistics(array $filters)
{
    // 复用 searchOrders 的筛选条件，size(0) 不返回文档只取聚合
    $search = static::searchOrders($filters)->size(0);

    // 按月统计销售额
    $search->aggs('monthly', function ($agg) {
        $agg->dateHistogram([
            'field'             => 'created_at',
            'calendar_interval' => 'month',
            'format'            => 'yyyy-MM',
            'time_zone'         => 'Asia/Shanghai',
        ]);
        $agg->aggs('revenue', fn ($a) => $a->sum('total_amount'));
    });

    // 按商家分组汇总
    $search->aggs('by_merchant', function ($agg) {
        $agg->terms('merchant_name');
        $agg->aggs('revenue', fn ($a) => $a->sum('total_amount'));
    });

    $results = $search->get();
    return $results->aggregations();
}
```

## 阶段 6：增量同步

订单状态变更、商家改名，ES 要跟着更新。触发方式可以是 ORM 事件、消息队列、binlog 监听等，最终都是同一个流程：**拿到文档 ID 列表 → 推队列异步处理**。

推入队列（不直接更新 ES）：

```php
// OrderIndex 中推队列
public static function syncOrders(array $ids)
{
    foreach (array_chunk($ids, 100) as $chunk) {
        Queue::push(SyncEsJob::class, ['class' => static::class, 'ids' => $chunk]);
    }
}

// 通过 binlog 监听、ORM 事件等触发，取到 doc_id 后异步更新
OrderIndex::syncOrders($orderIds);
```

通用的 SyncEsJob，所有 Index 复用：

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

        $bulk->execute();
        $job->delete();
    }
}
```

## 阶段 7：Schema 演进

上线后产品要加字段，比如新增"备注"。在 OrderIndex 中加上 mappings 和 source：

```php
// mappings 加字段
'remark' => ['type' => 'text'],

// source 的 yield 加字段
'remark' => $order['remark'],
```

然后 Rebuild：

```php
$rebuildStartTime = now();

$rebuild = new Rebuild(new OrderIndex());
$result = $rebuild->batchSize(500)->run();
$rebuild->clean($result['oldIndex']);

// Rebuild 期间 DB 仍在变更，新索引只是开始时刻的快照，按 updated_at 增量补全
$orderIds = Db::table('orders')
    ->where('updated_at', '>=', $rebuildStartTime)
    ->pluck('id');

OrderIndex::syncOrders($orderIds);
```

`run()` 自动完成：创建新索引 → 导入 → 别名切换，零停机。

---

→ [Index 文档](index.md)——完整 API 参考。

# ElasticKit

> 中文 | [English](README.md)

[![Tests](https://github.com/ykan821/ElasticKit/actions/workflows/ci.yml/badge.svg)](https://github.com/ykan821/ElasticKit/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/packagist/php-v/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![License](https://img.shields.io/packagist/l/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)

PHP Elasticsearch DSL 查询构建库，覆盖查询、聚合、CRUD、批量写入、零停机重建。

## 安装

```
composer require ykan/elastickit:^8
```

> 需要 PHP 8.1+、Elasticsearch 8.x。依赖 `elasticsearch-php` 自动安装。

## 快速开始

```php
use ElasticKit\Index\Index;

// 1. 注册 Client
Index::setClientResolver(
    fn () => \Elastic\Elasticsearch\ClientBuilder::create()
        ->setHosts(['http://localhost:9200'])->build()
);

// 2. 定义索引
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

// 3. 搜索
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->get();

$hits = $results->docs();   // [['title' => '...'], ...]
$total = $results->total(); // 索引未设 $trackTotalHits = true 时为 null（见分页与游标）
```

## DSL 示例

ElasticKit 的 DSL 尽量贴近 ES 原生 API，以减少认知负担——熟悉 ES DSL 的人可以平滑迁移。每个查询类型都是一个专门的 `Node` 类，方法名与 ES 参数对应。

<details>
<summary>展开查看</summary>

### 复合查询

```php
$results = ProductIndex::query()
    ->bool([
        'must'   => fn ($q) => $q->match('title', 'elasticsearch'),
        'filter' => fn ($q) => $q
            ->range('price', [10, 100])
            ->when($status, fn ($q) => $q->term('status', $status)),  // 条件过滤
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

### OOP 风格

先单独构造各个子句，再组合成查询：

```php
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Term;

// 构造子句
$status = Term::create('status', 'published')->boost(1.5);
$title  = Match_::create('title', 'elasticsearch');

// 组合成 bool 查询
$bool = Boolean::create()->must($title)->filter($status);
if ($filterByPrice) {
    $bool->filter(Range::create('price', [10, 100]));
}

// 执行
$results = ProductIndex::query()->bool($bool)->size(20)->get();
```

### 聚合

```php
$results = ProductIndex::query()
    ->matchAll()
    ->aggs('status_counts', fn ($agg) => $agg->terms('status'))
    ->aggs('price_stats', fn ($agg) => $agg->stats('price'))
    ->size(0)
    ->get();

$aggs = $results->aggregations();
```

### kNN 搜索

```php
$results = ProductIndex::query()
    ->knn(fn ($k) => $k
        ->field('embedding')
        ->queryVector([0.12, 0.45, 0.78, /* ... */])
        ->numCandidates(100))
    ->size(10)
    ->get();
```

### 原生数组

```php
$query = Query::create([
    'query' => [
        'bool' => [
            'must'   => fn ($q) => $q->match('title', 'elasticsearch'),  // 支持数组嵌套闭包
            'filter' => fn ($q) => $q->term('status', 'published'),
        ],
    ],
    'size' => 20,
    'sort' => [['price' => 'asc']],
]);
```

### 子句追加（ClausesSupport）

`bool` 查询的子句（must / should / filter / must_not）**累加追加**——重复调用会累加，而非覆盖：

```php
$q->bool(fn ($b) => $b->must($q1));   // must: [q1]
$q->bool(fn ($b) => $b->must($q2));   // must: [q1, q2]
```

> `dis_max`、`span_or`、`span_near` 等其他数组子句容器同理。

### 灵活入参

同一个方法接受多种入参形式——按场景选用：

```php
$q->term('status', 'published');                                     // string
$q->term(['status' => 'published']);                                 // array
$q->term(fn ($t) => $t->field('status')->value('published'));        // closure
$q->term(Term::create('status', 'published'));                       // object
```

</details>

## Index 示例

围绕 `Index` 基类，ElasticKit 为索引日常操作封装了专用类——分页、CRUD、批量写入、索引管理、零停机重建、事件监听。

<details>
<summary>展开查看</summary>

### 分页与游标

```php
// 分页
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->paginate($page, $perPage);

$results->lastPage();
$results->items();
$results->toPaginator();  // 转为框架分页器（需注册 Paginator Resolver）

// 分批遍历（大批量导出/批处理，每次 yield 一个 Results）
foreach (ProductIndex::query()->chunk() as $results) {
    foreach ($results->docs() as $doc) {
        // ...
    }
}

// 逐条遍历（导出/逐条加工，每次 yield 一个 hit：_id/_score/_source）
foreach (ProductIndex::query()->cursor() as $hit) {
    $doc = $hit['_source'];
    // ...
}
```

> **分页要总数，而总数默认关闭。** `Index` 默认 `$trackTotalHits = false`，`total()`/`lastPage()` 返 `null`、`toPaginator()` 抛异常。需要页码分页时在索引上设 `protected int|bool $trackTotalHits = true;`（或计数上限）；否则用 `hasMorePages()` / `chunk()` / `cursor()` 做无总数遍历。

### 文档 CRUD

```php
ProductIndex::doc(1)->save(['title' => 'Hello', 'price' => 99.9]);

$doc = ProductIndex::doc(1);
$doc->source();  // ['title' => 'Hello', 'price' => 99.9]

$doc->update(['price' => 89.9]);
$doc->delete();
```

### 批量操作

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

### 索引管理

```php
use ElasticKit\Index\Manager;

$manager = new Manager(new ProductIndex());

$manager->create();       // 创建索引
$manager->exists();       // bool
$manager->putMapping();   // 更新 mapping
$manager->delete();       // 删除索引
```

### 零停机重建

```php
use ElasticKit\Index\Rebuild;

// 1. 在 Index 子类中定义数据源
class ProductIndex extends Index
{
    public function source(array $context = []): iterable
    {
        foreach (Db::table('products')->cursor() as $row) {
            yield $row['id'] => $row;
        }
    }
}

// 2. 执行重建（自动创建新索引 → 导入 → 切换别名）
$result = (new Rebuild(new ProductIndex()))
    ->batchSize(500)
    ->run();

// $result = ['newIndex' => 'products_20260607_120000', 'oldIndex' => 'products_20260601_090000']

// 3. 清理旧索引或回滚
(new Rebuild(new ProductIndex()))->clean($result['oldIndex']);
(new Rebuild(new ProductIndex()))->rollback($result['oldIndex']);
```

### 事件监听

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

## 文档

- [实践指南](docs/guide.zh.md)——电商订单场景，从安装到上线的完整流程
- [Index 文档](docs/index.zh.md)——搜索、CRUD、批量操作、零停机重建、事件
- [更新日志](CHANGELOG.md)
- [Elasticsearch 官方文档](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html)——查询类型和参数参考

## License

MIT

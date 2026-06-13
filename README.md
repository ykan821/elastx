# ElasticKit

[![Latest Version](https://img.shields.io/packagist/v/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![Total Downloads](https://img.shields.io/packagist/dt/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)
[![License](https://img.shields.io/packagist/l/ykan/elastickit)](https://packagist.org/packages/ykan/elastickit)

PHP Elasticsearch DSL 查询构建库，覆盖查询、聚合、CRUD、批量写入、零停机重建。

## 安装

```
composer require ykan/elastickit:^8
```

> 需要 PHP 8.1+、Elasticsearch 8.x。依赖 `elasticsearch-php` 自动安装。ES 7.x 用户见 [7.x 分支](https://github.com/ykan821/ElasticKit/tree/7.x)。

## 快速开始

```php
use ElasticKit\Index\Index;

// 1. 注册 Client
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])->build();
Index::setClient($client);

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
$total = $results->total(); // 命中总数
```

## DSL 示例

<details>
<summary>展开查看</summary>

### 多态参数

同一个方法支持字符串、数组、闭包、对象四种写法：

```php
$q->term('status', 'published');                                     // string
$q->term(['status' => 'published']);                                 // array
$q->term(fn ($t) => $t->field('status')->value('published'));        // closure
$q->term(Term::create('status', 'published'));                       // object
```

### OOP 风格

每个查询类型都是独立的 Node 类，支持链式调用：

```php
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\Compound\Boolean;

$bool = Boolean::create()
    ->must(Match_::create('title', 'elasticsearch'))
    ->filter(Term::create('status', 'published')->boost(1.5));

// 增量构建
if ($filterByPrice) {
    $bool->filter(Range::create('price', [10, 100]));
}

$query = Query::create($bool);

$query->toArray();  // ['query' => ['bool' => [...]]]
$query->toJson();   // '{"query":{"bool":{...}}}'
```

### 复合查询

```php
$results = ProductIndex::query()
    ->bool([
        'must'   => fn ($q) => $q->match('title', 'elasticsearch'),
        'filter' => fn ($q) => $q
            ->range('price', [10, 100])
            ->when($status, fn ($q) => $q->term('status', $status))  // 条件过滤
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

### 嵌套查询

```php
$results = ProductIndex::query()
    ->nested('comments', fn ($q) => $q->match('comments.body', 'great'))
    ->get();
```

### 原生 DSL 透传

```php
// 支持原生数组嵌套闭包，query/aggs/参数可一次性传入
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

## Index 示例

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

// 游标遍历（大批量导出）
foreach (ProductIndex::query()->cursor() as $batch) {
    foreach ($batch->docs() as $doc) {
        // ...
    }
}
```

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
    ->execute();
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

// $result = ['newIndex' => 'products_20260607', 'oldIndex' => 'products_20260601']

// 3. 清理旧索引或回滚
(new Rebuild(new ProductIndex()))->clean($result['oldIndex']);
(new Rebuild(new ProductIndex()))->rollback($result['oldIndex']);
```

### 事件监听

```php
use ElasticKit\Index\Event;

ProductIndex::listen('search.query.after', function (Event $e) {
    Log::info("Search on {$e->index}", [
        'dsl' => $e->dsl,
        'duration' => $e->duration,
    ]);
});

ProductIndex::listen('search.*', function (Event $e) {
    Log::debug($e->name);
});
```

</details>

## 文档

- [实践指南](docs/guide.md)——电商订单场景，从安装到上线的完整流程
- [Index 文档](docs/index.md)——搜索、CRUD、批量操作、零停机重建、事件
- [升级指南](docs/upgrade.md)——v7.x → v8.x 迁移说明
- [更新日志](CHANGELOG.md)
- [Elasticsearch 官方文档](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html)——查询类型和参数参考

## AI 辅助开发

本项目使用 AI 辅助开发，核心路径和测试经人工审查。

## License

MIT

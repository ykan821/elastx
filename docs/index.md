# Index

Index 是抽象基类。继承它定义索引，注册 ES Client，然后查询。

## 配置

```php
use ElasticKit\Index\Index;

// 创建官方 Client
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])
    ->build();

// 注册为默认连接
Index::setClient($client);

// 多连接
Index::setClient($mainClient, 'main');
Index::setClient($logClient, 'logs');
```

定义索引：

```php
class ProductIndex extends Index
{
    protected string $name = 'products';       // 索引名（必填）
    protected array $mappings = [             // 索引 mappings
        'properties' => [
            'title'  => ['type' => 'text'],
            'price'  => ['type' => 'float'],
            'status' => ['type' => 'keyword'],
        ],
    ];
    protected array $settings = [             // 索引 settings
        'number_of_shards' => 1,
    ];
    protected string $connection = 'main';     // 连接名（默认 'default'）

    public function rebuildName(): string // 重建后的真实索引名（可重写自定义）
    {
        return $this->name . '_' . date('Ymd_His');
    }
}
```

## 搜索

`query()` 返回新的 Search 实例。链式调用 DSL 方法，然后执行：

```php
$results = ProductIndex::query()
    ->match('title', 'elasticsearch')
    ->sort('price', 'asc')
    ->size(20)
    ->get();

$results->total();        // 命中数
$results->docs();         // _source 数组
$results->hits();         // 完整 hit 数组
$results->aggregations(); // 聚合结果
```

```php
// 仅返回第一条（内部设置 size=1）
$doc = ProductIndex::query()->match('title', 'test')->first();

// 不获取文档，只统计数量
$total = ProductIndex::query()->term('status', 'published')->count();

// 聚合快捷方法（内部设置 size=0）
$avg = ProductIndex::query()->avg('price');
$max = ProductIndex::query()->max('price');
$min = ProductIndex::query()->min('price');
$sum = ProductIndex::query()->sum('price');
```

## 分页

```php
// 手动分页
$results = ProductIndex::query()->paginate($page, $perPage);

// 自动从请求解析
Index::setPageResolver(function () {
    return [request('page', 1), request('per_page', 20)];
});
$results = ProductIndex::query()->paginate();

// 对接框架分页器
Index::setPaginatorResolver(function ($results, $page, $perPage) {
    return new LengthAwarePaginator($results->docs(), $results->total(), $perPage, $page);
});
$results->toPaginator();
```

## Scroll

大数据集使用 scroll 分批获取：

```php
// 首批（默认 size=1000）
$results = ProductIndex::query()->size(500)->scroll();
$total = $results->total();
$scrollId = $results->scrollId();

// 继续获取
while (count($results->docs()) > 0) {
    // 处理 $results->docs()...
    $results = ProductIndex::query()->scroll($scrollId);
    $scrollId = $results->scrollId();
}

// 完成后清理
ProductIndex::query()->clear($scrollId);
```

## Cursor

Cursor 把 scroll 封装成 PHP 生成器：

```php
foreach (ProductIndex::query()->cursor() as $results) {
    foreach ($results->docs() as $doc) {
        // 处理
    }
}
// scroll 自动清理
```

## 文档 CRUD

```php
$doc = ProductIndex::doc(1);

$doc->create(['title' => 'New Product', 'price' => 29.99]);
$doc->source();   // 获取 _source 数组
$doc->update(['price' => 39.99]);

// 带冲突重试的更新
$doc->retryOnConflict(3)->update(['price' => 39.99]);

$doc->delete();
```

`update()` 默认不使用 upsert 语义——文档不存在时会报错。传入 `true` 启用 upsert：

```php
$doc->update(['price' => 39.99]);           // 文档不存在时报错
$doc->update(['price' => 39.99], true);     // 文档不存在时自动创建
```

## 批量操作

```php
use ElasticKit\Index\Bulk;

$bulk = new Bulk(new ProductIndex());
$bulk->batchSize(500);
$bulk->index(1, ['title' => 'Product A']);
$bulk->index(2, ['title' => 'Product B']);
$bulk->delete(3);
$bulk->execute(); // 执行所有操作，执行后清空状态
```

### 错误处理

`execute()` 默认在响应包含错误时抛出 `RuntimeException`。使用 `onError()` 自定义处理。回调接收 ES 原始响应，不抛出则继续，抛出则中断：

```php
$bulk = new Bulk(new ProductIndex());

// 不设 onError → 有错误就抛异常
$bulk->execute();

// 设 onError → 回调内不抛则继续，抛则中断
$bulk->onError(function (array $response) {
    $failures = count(array_filter($response['items'], fn($i) => isset($i['index']['error'])));
    if ($failures > 100) {
        throw new RuntimeException("失败超过阈值: {$failures}");
    }
    Log::warning("部分失败: {$failures} 条");
})->execute();
```

> `batchSize` 自动 flush 时的错误同样走 `onError`，不会丢失。

## 零停机重建

创建新索引 → 导入数据 → 切换别名。

`$name` 始终是应用面向的名称。应用不需要更改使用的名称——所有 CRUD、搜索、批量操作始终指向 `$name`。重建后，`$name` 变成别名，指向由 `rebuildName()` 生成的新索引。

```php
use ElasticKit\Index\Rebuild;

$rebuild = new Rebuild(new ProductIndex());

// 重建：返回新旧索引名
$result = $rebuild->batchSize(500)->run();
// $result = ['newIndex' => 'products_20250601_120000', 'oldIndex' => 'products_20250531_090000']

// 确认无误后清理旧索引
$rebuild->clean($result['oldIndex']);

// 或出问题时回滚
$rebuild->rollback($result['oldIndex']);
```

### 工作原理

`run()` 自动检测当前状态：

1. **$name 已是别名**（后续重建）：原子别名切换，零停机
2. **$name 是真实索引**：抛出 `RuntimeException`，必须先手动删除或转换为别名模式
3. **$name 不存在**：创建新索引并设置别名

重建后 `$name` 变成别名，指向 `rebuildName()` 生成的新索引。旧索引保留，由你决定 `clean()` 或 `rollback()`。

### 自定义命名

重写 `rebuildName()` 自定义新索引命名：

```php
class ProductIndex extends Index
{
    public function rebuildName(): string
    {
        return $this->name . '_v' . time();
    }
}
```

### 数据源

在 Index 子类中重写 `source()` 提供重建数据。基类未重写时会抛异常：

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

也可以在调用时传入自定义数据源：

```php
$rebuild->source(function () {
    yield 1 => ['title' => 'test'];
})->run();
```

`run()` 接受可选的 `$context` 参数，传递给 `source()`：

```php
$rebuild->run(['after' => '2024-01-01']);
```

### 错误处理

Rebuild 内部使用 Bulk 执行导入，`onError()` 用法与 [批量操作 > 错误处理](#错误处理) 一致：

```php
$rebuild->onError(function (array $response) {
    Log::warning("重建导入错误", $response);
})->run();
```

> rebuild 期间 DB 仍在变更，新索引是开始时刻的快照，建议 rebuild 后通过 `updated_at` 增量同步补齐。
>
> 新增字段后，对尚未建立 mapping 的字段执行 sort、agg、collapse 等操作会报错，需评估是否先 `putMapping()` 再部署。修改/删除字段需分阶段部署。

## 参考

### Manager

ES indices API 的薄代理。`new Manager($index)`，不会给 Index 添加方法：

```php
use ElasticKit\Index\Manager;

$manager = new Manager(new ProductIndex());
```

### 事件

```php
use ElasticKit\Index\Event;

Index::listen('search.query.after', function (Event $e) {
    Log::info("{$e->name} on {$e->index}", ['duration' => $e->duration]);
});

// 通配符
Index::listen('search.*', function (Event $e) { ... });
Index::listen('*', function (Event $e) { ... });
```

所有事件携带 `$name` 和 `$index`。

### 自定义 Client

使用 `ClientBuilder` 配置客户端（主机、SSL、日志等）：

```php
$client = \Elastic\Elasticsearch\ClientBuilder::create()
    ->setHosts(['https://localhost:9200'])
    ->setLogger($logger)
    ->build();

Index::setClient($client);
```

## 安全

以下方法接受原生 ES 参数，**不得**直接接收用户输入：

- `script()`, `scriptScore()`, `scriptFields()`, `runtimeMappings()` — Painless 脚本执行
- `Bulk::target()` — 目标索引覆盖
- `sort()` 使用 `_script` 类型 — 通过排序执行脚本
- `postFilter()` — 原生查询透传

务必在传入 DSL 方法前验证和过滤用户输入。

## 速查表

### Manager 方法

| 方法 | 说明 |
|------|------|
| `create()` | 创建索引（含 mappings 和 settings） |
| `delete()` | 删除索引 |
| `exists()` | 检查索引是否存在 |
| `get()` | 获取索引信息 |
| `open()` | 打开索引 |
| `close()` | 关闭索引 |
| `putMapping()` | 更新索引 mappings（使用 Index 定义） |
| `getMapping()` | 获取索引 mappings |
| `putSettings($settings)` | 更新索引 settings |
| `getSettings()` | 获取索引 settings |
| `refresh()` | 刷新索引 |
| `forceMerge()` | 强制合并索引段 |
| `addAlias($alias)` | 添加别名 |
| `removeAlias($alias)` | 移除别名 |
| `swapAlias($alias, $target)` | 切换别名指向 |
| `getAliases()` | 获取索引别名 |

### 事件列表

所有事件携带 `$name` 和 `$index`。`$action` 是调用方法名：`get`、`first`、`count`、`scroll` 或 `paginate`。

| 事件 | 属性 |
|------|------|
| `search.query.before` | `$dsl`, `$action` |
| `search.query.after` | `$dsl`, `$response`, `$duration`, `$action` |
| `search.scroll.before` | `$action`, `$scrollId` |
| `search.scroll.after` | `$action`, `$scrollId`, `$response`, `$duration` |
| `bulk.execute.before` | `$actions` |
| `bulk.execute.after` | `$actions`, `$response`, `$duration` |
| `manager.create.before` | |
| `manager.create.after` | `$response` |
| `manager.delete.before` | |
| `manager.delete.after` | `$response` |
| `manager.swap_alias.before` | |
| `manager.swap_alias.after` | `$response` |
| `rebuild.run.before` | |
| `rebuild.run.after` | `$newIndex`, `$oldIndex` |

# ElasticKit - Elasticsearch DSL Query Builder

PHP Elasticsearch DSL 查询构建库。

> 本文件提交到仓库。本地环境变量放在 `CLAUDE.local.md`（已 gitignore），Claude Code 自动加载两者。

**版本管理：** a.b.c，a 对齐 ES 主版本，`^8` 即可。

> master 对应 v8.x（ES 8.x，PHP 8.1+），7.x 分支独立维护（ES 7.x，PHP 7.2+）。两条线不互合并，CLAUDE.md 各分支独立维护。

### 提交信息规范

- **参数名锁定**：公开方法参数名是 API 的一部分（支持命名参数），minor 版本禁止重命名

[Conventional Commits](https://www.conventionalcommits.org/)，中文描述：`feat(query): 新增 knn 向量搜索`

Scope 可选：dsl / index / agg / query / docs。Breaking change 加 `!` 后缀。

### Changelog 规范

[Keep a Changelog](https://keepachangelog.com)，中文分类：

- **新增** / **变更** / **弃用** / **移除** / **修复** / **安全**
- 只记录对用户有影响的变更
- 相关改动合并为一条
- Breaking change 以 `**BC:**` 前缀标记

### 发版流程

1. 跑全部测试
2. 更新 CHANGELOG.md
3. 提交并推送
4. 确认版本号后打 tag 并推送

### PHPDoc 规范

PSR-5 规范。

## 待办

- [x] **clone query 后 try-finally 恢复**：first() / paginate() 等方法 clone query 后修改状态，需 try-finally 确保恢复
- [x] **ClausesSupport API 统一**：must()/should()/filter() 等改为追加语义，移除 addXXX() 方法
- [x] **hasMore() 确认无 bug**：scroll 场景 !empty(hits) 正确，分页应用 page()<lastPage()
- [x] **Rebuild::rollback() 支持多索引别名**：遍历全部 backing index 移除别名，对齐 doRun() 写法
- [x] **Index::$name 空验证**：name() 未设置时抛异常
- [x] **拆分静态状态**：ClientManager / EventDispatcher / Pagination 从 Index 拆出，删 deprecated 代理
- [x] **移除 Index::insert()**：等价功能由 Doc::save() 覆盖
- [x] **实例入口**：新增 on()/newQuery()/newDoc()/setConnection()/getConnection()，query()/doc() 委托实例方法
- [x] **测试 mock 污染修复**：所有测试文件补 tearDown 清理静态状态
- [ ] **命名参数一致性**：公开方法参数名是 API 的一部分，全库审查确保命名统一（如 connection/name/client 不混用）
- [x] **PHP 8 现代化（Index 层）**：全 12 文件 strict_types + 构造器提升 + readonly + 属性/返回/参数类型 + 联合类型；callable 属性（resolver / errorHandler / dataSource）因 PHP 禁止 callable 作属性类型，保留 docblock
- [ ] **PHP 8 现代化（DSL 层）**：Node/Query/Agg + 122 leaf 类。strict_types + 类型声明；`$_key`/`$_valueKey`/`$_isPropertyField`/`$_multi` 4 属性加类型需同步全部 leaf（原子操作，漏一个 fatal），field()/create()/boost()/toArray() 加返回类型需同步所有覆写；`$_properties`/`$_rawValue` 三模式统一留给批次 1
- [ ] **Rebuild 异常处理**：run() 改为 try-catch + releaseLock 分离，releaseLock 不吞任何异常，forceUnlock 单独处理 404（文档不存在 vs 索引不存在的 404 需区分）；isLocked() 只吞 404 不吞其他 ClientResponseException；rebuild 失败优先抛原始异常；ensureLockIndex() replicas=0 在多节点集群有风险需注释说明
- [x] **$client 抽到 Registry 类**：拆为 ClientManager / EventDispatcher / Pagination，Index 不再持有静态状态
- [x] **Node 构造函数重构**：拆分为 fromKeyValue/fromClosure/fromArrayField/fromScalar
- [x] **Bulk/Rebuild onError 设计**：Bulk 加 onError(callback) 默认 throw，Rebuild 删 skipErrors 加 onError，删 rebuild.import.failed 事件
- [ ] **补核心路径的边界测试**：scroll、bulk 分批、rebuild 失败回滚
- [ ] **搭建集成测试基建**：`ELASTICKIT_TEST_HOST` 驱动，随机索引名隔离
- [ ] **cursor/chunk API 重构**：现 `cursor()` 返回批次（Results）命名不准。拆为 `chunk($duration): Generator<Results>`（按批，即现 cursor 改名）+ `cursor($duration): Generator`（逐条 doc，内部扁平化 chunk、复用其 finally clear）；保留 `scroll()/next()/clear()` 作低层原语。待定：单条 yield 完整 hit（带 _id/_score）还是只 _source；底层未来可换 PIT+search_after（上层签名不变）

## 测试

测试在 Docker 容器中运行，需要设置以下环境变量：

| 变量 | 用途 |
|---|---|
| `PHP_CONTAINER` | Docker 容器名 |
| `PROJECT_PATH` | 项目在容器内的路径 |
| `PROXY_PORT` | HTTP 代理端口（推送用） |
| `ELASTICKIT_TEST_HOST` | ES 集成测试地址（如 `https://localhost:9200`），不设置则跳过集成测试 |

## 推送代码前需要执行4件套

```bash
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit --testsuite unit"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit --testsuite index"
docker exec -e ELASTICKIT_TEST_HOST=https://elasticsearch:9200 $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit --testsuite integration"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpstan analyse"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpmd src text phpmd.xml"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --diff"

# GitHub 不可达时走代理
https_proxy=http://127.0.0.1:$PROXY_PORT http_proxy=http://127.0.0.1:$PROXY_PORT git push
```

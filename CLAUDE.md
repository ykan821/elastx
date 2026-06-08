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

- [ ] **clone query 后 try-finally 恢复**：first() / paginate() 等方法 clone query 后修改状态，需 try-finally 确保恢复
- [ ] **ClausesSupport API 统一**：废弃 addXXX() 方法，原 API（如 must()）直接使用追加语义
- [ ] **hasMore() 判断逻辑修复**：待确认（看代码）
- [ ] **Rebuild::rollback() 支持多索引别名**：当前只移除 `$currentList[0]`，应遍历全部 remove，参考 `doRun()` 循环写法
- [ ] **Index::$name 空验证**：子类未设置 `$name` 时抛异常
- [ ] **锁抽取为独立类**：通用分布式锁（acquire/release/forceUnlock/isLocked + ensureLockIndex），Bulk 等可复用
- [ ] **$client 抽到 Registry 类**：新建 Registry 持有 client / pageResolver / paginatorResolver / listeners，Index 不再持有静态状态。旧 API 保留作 deprecated 代理，前期统一放 Registry，后续按需拆分
- [ ] **Node 构造函数重构**：提取 applyValue，消除重复
- [ ] **Bulk skipErrors() 设计**：参考 Rebuild 的 skipErrors 模式
- [ ] **补核心路径的边界测试**：scroll、bulk 分批、rebuild 失败回滚
- [ ] **搭建集成测试基建**：`ELASTICKIT_TEST_HOST` 驱动，随机索引名隔离

## 测试

测试在 Docker 容器中运行，需要设置以下环境变量：

| 变量 | 用途 |
|---|---|
| `PHP_CONTAINER` | Docker 容器名 |
| `PROJECT_PATH` | 项目在容器内的路径 |
| `PROXY_PORT` | HTTP 代理端口（推送用） |
| `ELASTICKIT_TEST_HOST` | ES 集成测试地址（如 `https://localhost:9200`），不设置则跳过集成测试 |

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

# ElasticKit - Elasticsearch DSL Query Builder

A PHP Elasticsearch DSL query builder library.

> This file is committed to the repository. Local environment variables live in `CLAUDE.local.md` (gitignored); Claude Code loads both automatically.

**Versioning:** a.b.c, where `a` tracks the ES major version. `^8` suffices.

> `master` tracks v8.x (ES 8.x, PHP 8.1+); the 7.x branch is maintained separately (ES 7.x, PHP 7.2+). The two lines are never merged into each other; CLAUDE.md is maintained per branch.

### Commit message conventions

- **Parameter names are locked**: public method parameter names are part of the API (named arguments are supported); renaming is forbidden in minor versions

[Conventional Commits](https://www.conventionalcommits.org/), with an English description: `feat(query): add knn vector search`

Scope is optional: dsl / index / agg / query / docs. Append `!` for breaking changes.

### Changelog conventions

[Keep a Changelog](https://keepachangelog.com), with English categories:

- **Added** / **Changed** / **Deprecated** / **Removed** / **Fixed** / **Security**
- Record only user-facing changes
- Merge related changes into a single entry
- Mark breaking changes with the `**BC:**` prefix

### Release flow

1. Run the full test suite
2. Update CHANGELOG.md
3. Commit and push
4. Confirm the version, then tag and push

### PHPDoc conventions

Follow PSR-5.

## TODO

- [ ] **Add boundary tests for core paths**: scroll, bulk batching, rebuild failure rollback
- [ ] **Set up integration test infrastructure**: driven by `ELASTICKIT_TEST_HOST`, with random index names for isolation

## Tests

Tests run inside a Docker container and require these environment variables:

| Variable | Purpose |
|---|---|
| `PHP_CONTAINER` | Docker container name |
| `PROJECT_PATH` | Project path inside the container |
| `PROXY_PORT` | HTTP proxy port (for pushing) |
| `ELASTICKIT_TEST_HOST` | ES endpoint for integration tests (e.g. `https://localhost:9200`); integration tests are skipped when unset |

## Pre-push checklist

```bash
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit --testsuite unit"
docker exec -e ELASTICKIT_TEST_HOST=https://elasticsearch:9200 $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit --testsuite integration"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpunit"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpstan analyse --memory-limit=256M"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && vendor/bin/phpmd src text phpmd.xml"
docker exec $PHP_CONTAINER sh -c "cd $PROJECT_PATH && PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --diff"

# Route through the proxy when GitHub is unreachable
https_proxy=http://127.0.0.1:$PROXY_PORT http_proxy=http://127.0.0.1:$PROXY_PORT git push
```

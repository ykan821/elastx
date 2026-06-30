# Changelog

## [8.0.0] - 2026-06-30

First stable release. The public API is now frozen: public method parameter
names are part of the API (named arguments are supported) and will not change
in 8.x patch/minor releases.

### Changed

- `Doc::refresh()` now accepts `bool` (`true`/`false`) in addition to `string` (`'wait_for'`), matching Elasticsearch's `refresh` parameter.

See the 8.0.0-beta.5 and 8.0.0-beta.4 entries for the full feature set and breaking changes introduced during the beta cycle.

## [8.0.0-beta.5] - 2026-06-28

### Added

- `Index::$trackTotalHits` (int|bool, default `false`) — opt-in total-hit tracking per index; `true` counts every hit, an int caps the count, `false` omits it.
- `Results::hasMorePages()` — next-page signal that works with or without a total (full-page heuristic when `track_total_hits` is false).
- `PaginationTotalUnavailableException` (in `ElasticKit\Index\Exception`) — thrown by `Results::toPaginator()` when no total is available.

### Changed

- **BC:** `Index` now defaults `track_total_hits` to `false`, so Elasticsearch omits the hit total. `Results::total()`/`lastPage()` return `?int` (null when the total is unavailable), and `toPaginator()` throws unless the index sets `$trackTotalHits = true` (or a count cap). Set `protected int|bool $trackTotalHits = true;` on indexes that paginate.
- `Results::isEmpty()` docblock now points to `hasMorePages()` for "has next page".

### Fixed

- `Node::toArray()` (and field-keyed overrides such as `Intervals`) throw `LogicException` when a field-keyed node has no field set, instead of an uncatchable typed-property `Error`.
- `Agg` empty aggregation body serializes to `{}`, not `[]` (which Elasticsearch rejects).
- `RangeSupport` rejects positional elements beyond the `[start, end]` shorthand instead of leaking them as numeric keys.
- `SpanTerm::term()` emits Elasticsearch's `{value}` key (was `{term}`).
- `Rebuild` alias-swap failures now delete the orphaned new index.

### Deprecated

- `DateHistogram::interval()` — Elasticsearch deprecated the bare `interval` key; use `calendarInterval()`/`fixedInterval()`.

### Removed

- Composer scripts (`analyse` / `cs-check` / `cs-fix`) — run the binaries via your Docker workflow instead.

## [8.0.0-beta.4] - 2026-06-07

### Added

- DSL query builder with polymorphic parameters (string/array/closure/object)
- Full query-type coverage: TermLevel, FullText, Compound, Geo, Joining, Span, Shape, Specialized
- Aggregations: Bucket, Metric, and Pipeline categories
- Search parameters: sort, highlight, rescore, collapse, suggest, post_filter, knn, etc.
- Index layer: CRUD, pagination, cursor iteration, bulk writes (Bulk), zero-downtime rebuild (Rebuild)
- Event system: listeners for each phase of search, bulk operations, and rebuild
- OOP style: each query/aggregation type is a dedicated Node class, supporting chaining and incremental building
- Raw DSL pass-through: uncovered ES features can be passed directly as arrays

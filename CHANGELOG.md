# Changelog

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

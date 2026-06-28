<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

use Closure;
use RuntimeException;
use ElasticKit\DSL\Queries\Compound;
use ElasticKit\DSL\Queries\FullText;
use ElasticKit\DSL\Queries\Geo;
use ElasticKit\DSL\Queries\Joining;
use ElasticKit\DSL\Queries\MatchAll;
use ElasticKit\DSL\Queries\Shape;
use ElasticKit\DSL\Queries\Span;
use ElasticKit\DSL\Support\RegistersAgg;
use ElasticKit\DSL\Queries\Specialized;
use ElasticKit\DSL\Queries\TermLevel;
use stdClass;

/**
 * Query container that combines multiple query conditions into an Elasticsearch DSL query.
 *
 * @phpstan-consistent-constructor
 */
class Query extends Node
{
    use Compound;
    use RegistersAgg;
    use FullText;
    use Geo;
    use Shape;
    use Joining;
    use MatchAll;
    use Span;
    use Specialized;
    use TermLevel;
    use Param;

    /**
     * @var string
     */
    protected string $_key = 'query';

    /**
     * @var bool
     */
    protected bool $_multi = false;

    /**
     * Query clauses stored independently from type properties.
     *
     * @var array<int, mixed>
     */
    protected array $_queries = [];

    /**
     * Aggregation nodes stored independently from type properties.
     *
     * @var array<string, Agg>
     */
    protected array $_aggregations = [];

    /**
     * Initialize the query container.
     *
     * Overrides Node constructor to handle Query-specific input forms:
     * - Node instance: wraps as a query clause via addQuery()
     * - Array with 'query' key: stored as raw ES body
     * - Array without 'query' key: wrapped as a query clause
     * - Closure: executed on $this
     * - null: empty container
     *
     * @param mixed $field
     * @param mixed $value
     */
    public function __construct($field = null, $value = null)
    {
        if ($value !== null) {
            parent::__construct($field, $value);
            return;
        }
        if ($field instanceof Closure) {
            $this->fromClosure($field);
        } elseif ($field instanceof Node) {
            $this->_queries[] = $field;
        } elseif (is_array($field)) {
            $this->fromArray($field);
        }
    }

    /**
     * Initialize from a raw ES body array.
     *
     * Arrays with a 'query' key are stored as-is (raw DSL).
     * Other arrays are added as query clauses.
     *
     * @param array<string, mixed> $field
     */
    protected function fromArray(array $field): void
    {
        if (array_key_exists('query', $field)) {
            $this->_properties = $field;
        } else {
            $this->_queries[] = $field;
        }
    }

    /**
     * Get all query clauses.
     *
     * @return array<int, mixed>
     */
    public function getQueries(): array
    {
        return $this->_queries;
    }

    /**
     * Add a query clause to the query container.
     *
     * @param Query|\Closure|array<string, mixed>|Node $clause
     * @return $this
     */
    public function addQuery($clause): static
    {
        $this->_queries[] = $clause;
        return $this;
    }

    /**
     * Conditionally add a query clause.
     *
     * $condition is a bool, or a Closure returning a bool.
     *
     * @param bool|\Closure $condition
     * @param Query|\Closure|array<string, mixed> $query
     * @param Query|\Closure|array<string, mixed>|null $default
     * @return $this
     */
    public function when(bool|\Closure $condition, $query, $default = null): static
    {
        $truthy = $condition instanceof \Closure ? $condition() : $condition;

        if ($truthy) {
            $this->addQuery(static::create($query));
        } elseif ($default !== null) {
            $this->addQuery(static::create($default));
        }

        return $this;
    }

    /**
     * Add an aggregation.
     *
     * - String $alias + Closure: creates Agg, passes to closure, returns $this.
     * - String $alias + Agg instance: registers directly, returns $this.
     * - String $alias + Array: wraps as raw DSL, returns $this.
     * - Agg instance as $alias (no $aggs): registers directly, returns $this.
     *
     * @param string|Agg|array<string, mixed> $alias
     * @param callable|Agg|array<string, mixed>|null $aggs
     * @return static
     */
    public function aggs($alias, $aggs = null): static
    {
        return $this->registerAgg($alias, $aggs, $this->_aggregations);
    }

    /**
     * Serialize the query container to an Elasticsearch DSL array.
     *
     * When constructed from a raw ES body array, returns it directly.
     * Otherwise builds from query clauses, aggregations, and params.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $dsl = $this->_properties !== null ? $this->resolveProperties($this->_properties) : [];

        $query = $this->buildQuery();
        if ($this->_multi || !empty($query)) {
            $dsl['query'] = $query;
        }

        $dsl = $this->buildAggs($dsl);
        $dsl = $this->buildParams($dsl);

        return array_filter($dsl, fn ($v) => $v !== null);
    }

    /**
     * Build the query clause array from stored query clauses.
     *
     * @return array<int|string, mixed>
     */
    private function buildQuery(): array
    {
        if (empty($this->_queries)) {
            return [];
        }

        // Flatten nested Query instances
        $flat = [];
        foreach ($this->_queries as $item) {
            if ($item instanceof self) {
                foreach ($item->getQueries() as $clause) {
                    $flat[] = $clause;
                }
            } else {
                $flat[] = $item;
            }
        }

        $clauses = $this->buildClauses($flat);

        if ($this->_multi) {
            return $clauses;
        }

        return $this->mergeClauses($clauses);
    }

    /**
     * Build clause entries from flattened queries, skipping nodes that
     * serialize to null/[] (e.g. an empty bool built dynamically).
     *
     * @param array<int, mixed> $flat
     * @return array<int, array<string, mixed>>
     */
    private function buildClauses(array $flat): array
    {
        $clauses = [];
        foreach ($flat as $query) {
            if ($query instanceof Node) {
                $body = $query->toArray();
                if ($body === null) {
                    continue;
                }
                $clauses[] = [$query->key() => $body];
            } elseif (is_array($query)) {
                foreach ($query as $field => $item) {
                    if ($item instanceof Node) {
                        $item = $item->toArray();
                    }
                    if ($item === null) {
                        continue;
                    }
                    $clauses[] = [$field => $item];
                }
            } else {
                throw new RuntimeException(sprintf(
                    'Unsupported clause type %s; use a Node, array, or closure.',
                    get_debug_type($query)
                ));
            }
        }

        return $clauses;
    }

    /**
     * Merge per-clause arrays, throwing on duplicate keys instead of silently overwriting.
     *
     * @param array<int, array<string, mixed>> $clauses
     * @return array<string, mixed>
     * @throws RuntimeException when two clauses share a key
     */
    private function mergeClauses(array $clauses): array
    {
        $merged = [];
        foreach ($clauses as $clause) {
            $clash = array_intersect_key($merged, $clause);
            if ($clash) {
                throw new RuntimeException(sprintf('Duplicate query clause key "%s".', implode('", "', array_keys($clash))));
            }
            $merged += $clause;
        }

        return $merged;
    }

    /**
     * Build aggregation entries into the DSL array.
     *
     * @param array<string, mixed> $dsl
     * @return array<string, mixed>
     */
    private function buildAggs(array $dsl): array
    {
        if (empty($this->_aggregations)) {
            return $dsl;
        }
        $dsl['aggs'] = [];
        foreach ($this->_aggregations as $agg) {
            $dsl['aggs'] += $agg->toArray();
        }
        return $dsl;
    }

    /**
     * Build search request parameters into the DSL array.
     *
     * @param array<string, mixed> $dsl
     * @return array<string, mixed>
     */
    private function buildParams(array $dsl): array
    {
        foreach ($this->_params as $key => $value) {
            if ($value instanceof Query) {
                $value = $value->toArray()['query'] ?? new stdClass();
            } elseif ($value instanceof Node) {
                $value = $value->toArray();
            } elseif (is_array($value)) {
                $value = array_map(fn ($v) => $v instanceof Node ? $v->toArray() : $v, $value);
            }
            $dsl[$key] = $value;
        }
        return $dsl;
    }
}

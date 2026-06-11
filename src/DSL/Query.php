<?php

namespace ElasticKit\DSL;

use BadMethodCallException;
use Closure;
use ElasticKit\DSL\Queries\Compound;
use ElasticKit\DSL\Queries\FullText;
use ElasticKit\DSL\Queries\Geo;
use ElasticKit\DSL\Queries\Joining;
use ElasticKit\DSL\Queries\MatchAll;
use ElasticKit\DSL\Queries\Shape;
use ElasticKit\DSL\Queries\Span;
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
    protected $_key = 'query';

    /**
     * @var bool
     */
    protected $_multi = false;

    /**
     * Query clauses stored independently from type properties.
     *
     * @var array<int, mixed>
     */
    protected $_queries = [];

    /**
     * Aggregation nodes stored independently from type properties.
     *
     * @var array<string, Agg>
     */
    protected $_aggregations = [];

    /**
     * Set whether the query supports multiple clauses.
     *
     * @param bool $multi
     * @return static
     */
    protected function setMulti($multi)
    {
        $this->_multi = $multi;
        return $this;
    }

    /**
     * Whether the query supports multiple clauses.
     *
     * @return bool
     */
    protected function isMulti()
    {
        return $this->_multi;
    }

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
            $field($this);
        } elseif ($field instanceof Node) {
            $this->_queries[] = $field;
        } elseif (is_array($field)) {
            if (array_key_exists('query', $field)) {
                $this->_properties = $field;
            } else {
                $this->_queries[] = $field;
            }
        } elseif ($field !== null) {
            $this->_properties = $field;
        }
    }

    /**
     * Get all query clauses.
     *
     * @return array<int, mixed>
     */
    public function getQueries()
    {
        return $this->_queries;
    }

    /**
     * Add a query clause to the query container.
     *
     * @param mixed $query
     * @return $this
     */
    public function addQuery($query)
    {
        $this->_queries[] = $query;
        return $this;
    }

    /**
     * Conditionally add a query clause.
     *
     * @param bool|callable $condition
     * @param mixed $query
     * @param mixed $default
     * @return $this
     */
    public function when($condition, $query, $default = null)
    {
        $truthy = is_callable($condition) ? $condition() : $condition;

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
     * @return $this
     * @throws \BadMethodCallException if called with a string alias and no definition
     */
    public function aggs($alias, $aggs = null)
    {
        if ($aggs === null && !is_string($alias)) {
            $aggs = $alias;
            $alias = null;
        }

        if ($aggs instanceof Agg) {
            if ($alias !== null) {
                $aggs->alias($alias);
            }
            $this->_aggregations[$alias ?? $aggs->getAlias()] = $aggs;
            return $this;
        }

        if (is_array($aggs)) {
            $childAgg = Agg::create($aggs);
            if ($alias !== null) {
                $childAgg->alias($alias);
            }
            $this->_aggregations[$alias] = $childAgg;
            return $this;
        }

        if ($alias !== null && !isset($this->_aggregations[$alias])) {
            $this->_aggregations[$alias] = new Agg();
            $this->_aggregations[$alias]->alias($alias);
        }

        if ($aggs instanceof \Closure) {
            $aggs($this->_aggregations[$alias]);
            return $this;
        }

        if ($alias !== null) {
            throw new BadMethodCallException(
                sprintf('aggs() requires a second argument. Use aggs("%s", $definition) where $definition is a closure, array, or Agg instance.', $alias)
            );
        }

        return $this;
    }

    /**
     * Serialize the query container to an Elasticsearch DSL array.
     *
     * When constructed from a raw ES body array, returns it directly.
     * Otherwise builds from query clauses, aggregations, and params.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $dsl = is_array($this->_properties) ? $this->resolveProperties($this->_properties) : [];

        $query = $this->buildQuery();
        if (!empty($query)) {
            $dsl['query'] = $query;
        }

        $this->buildAggs($dsl);
        $this->buildParams($dsl);

        return array_filter($dsl, function ($v) {
            return $v !== [];
        });
    }

    /**
     * Build the query clause array from stored query clauses.
     *
     * @return array<string, mixed>|object
     */
    private function buildQuery()
    {
        if (empty($this->_queries)) {
            return $this->_multi ? (object)[] : [];
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

        $clauses = [];
        foreach ($flat as $query) {
            if ($query instanceof Node) {
                $clauses[] = [$query->key() => $query->toArray()];
            } elseif (is_array($query)) {
                foreach ($query as $field => $item) {
                    if ($item instanceof Node) {
                        $item = $item->toArray();
                    }
                    $clauses[] = [$field => $item];
                }
            }
        }

        if ($this->_multi) {
            return $clauses; // @phpstan-ignore return.type
        }
        if (empty($clauses)) {
            return [];
        }
        return array_merge(...$clauses);
    }

    /**
     * Build aggregation entries into the DSL array.
     *
     * @param array<string, mixed> $dsl
     * @return void
     */
    private function buildAggs(array &$dsl)
    {
        if (empty($this->_aggregations)) {
            return;
        }
        $dsl['aggs'] = [];
        foreach ($this->_aggregations as $agg) {
            $dsl['aggs'] += $agg->toArray();
        }
    }

    /**
     * Build search request parameters into the DSL array.
     *
     * @param array<string, mixed> $dsl
     * @return void
     */
    private function buildParams(array &$dsl)
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
    }
}

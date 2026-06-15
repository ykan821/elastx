<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

use BadMethodCallException;
use ElasticKit\DSL\Aggs\Bucket;
use ElasticKit\DSL\Aggs\Metric;
use ElasticKit\DSL\Aggs\Pipeline;

/**
 * Aggregation container (independent, does not extend Node).
 *
 * @phpstan-consistent-constructor
 */
class Agg
{
    use Bucket;
    use Metric;
    use Pipeline;

    /**
     * The aggregation type node.
     */
    protected ?Node $_node = null;

    /**
     * The alias name that wraps this aggregation in DSL output.
     */
    protected ?string $_alias = null;

    /**
     * Nested sub-aggregations keyed by alias.
     *
     * @var array<string, Agg>
     */
    protected array $subAggs = [];

    /**
     * Properties for array-based aggregation definitions (raw DSL mode).
     * Null when the aggregation is node-based or empty.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $_properties = null;

    /**
     * Static factory — thin proxy over the constructor.
     *
     * Only intercepts same-class instance reuse; everything else
     * delegates to the constructor.
     *
     * @param mixed $agg
     * @return static
     */
    public static function create($agg = []): static
    {
        if ($agg instanceof static) {
            return $agg;
        }

        if ($agg instanceof \Closure) {
            $newAgg = new static();
            $agg($newAgg);
            return $newAgg;
        }

        if (is_array($agg)) {
            $newAgg = new static();
            $newAgg->_properties = $agg;
            return $newAgg;
        }

        return new static();
    }

    /**
     * Set the aggregation type via a Node instance.
     *
     * @param Node $node
     * @return $this
     */
    protected function node($node): static
    {
        $this->_node = $node;
        $this->_properties = null;
        return $this;
    }

    /**
     * Set the alias name that wraps this aggregation in DSL output.
     *
     * Similar to Node::field(), the alias wraps the output:
     * {"alias_name": {"terms": {"field": "status"}}}.
     *
     * @param string $alias
     * @return $this
     */
    public function alias($alias): static
    {
        $this->_alias = $alias;
        return $this;
    }

    /**
     * Get the alias name.
     *
     * @return string|null
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Add a nested sub-aggregation.
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
    public function aggs($alias, $aggs = null): static
    {
        if ($aggs === null && !is_string($alias)) {
            $aggs = $alias;
            $alias = null;
        }

        if ($aggs instanceof Agg) {
            if ($alias !== null) {
                $aggs->alias($alias);
            }
            $this->subAggs[$alias ?? $aggs->getAlias()] = $aggs;
            return $this;
        }

        if (is_array($aggs)) {
            $childAgg = Agg::create($aggs);
            if ($alias !== null) {
                $childAgg->alias($alias);
            }
            $this->subAggs[$alias] = $childAgg;
            return $this;
        }

        if ($alias !== null && !isset($this->subAggs[$alias])) {
            $this->subAggs[$alias] = new Agg();
            $this->subAggs[$alias]->alias($alias);
        }

        if ($aggs instanceof \Closure) {
            $aggs($this->subAggs[$alias]);
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
     * Resolve nested Query, Node, and Agg instances in a properties array.
     *
     * @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    protected function resolveProperties(array $properties)
    {
        foreach ($properties as $key => $property) {
            if ($property instanceof Query) {
                $properties[$key] = $property->toArray()['query'];
            } elseif ($property instanceof Agg) {
                $properties[$key] = $property->toArray();
            } elseif ($property instanceof Node) {
                $properties[$key] = $property->toArray();
            }
        }
        return $properties;
    }

    /**
     * Serialize to an Elasticsearch aggregation DSL array.
     *
     * When $_alias is set, wraps the output under the alias name
     * (similar to Node's field wrapping).
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        if ($this->_properties !== null) {
            $resolved = $this->resolveProperties($this->_properties);
            if ($this->_alias !== null) {
                return [$this->_alias => $resolved];
            }
            return $resolved;
        }

        $inner = [];

        if ($this->_node instanceof Node) {
            $inner[$this->_node->key()] = $this->_node->toArray();
        }

        if (!empty($this->subAggs)) {
            $inner['aggs'] = [];
            foreach ($this->subAggs as $subAgg) {
                $inner['aggs'] += $subAgg->toArray();
            }
        }

        if ($this->_alias !== null) {
            return [$this->_alias => $inner];
        }

        return $inner;
    }

    /**
     * Convert the aggregation to a JSON string.
     *
     * @param int $flags
     * @param int $depth
     * @return string
     */
    public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, int $depth = 512): string
    {
        $json = json_encode($this->toArray(), $flags, $depth);

        return $json === false ? '' : $json;
    }

    /**
     * Convert the aggregation to a JSON string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

use ElasticKit\DSL\Aggs\Bucket;
use ElasticKit\DSL\Aggs\Metric;
use ElasticKit\DSL\Aggs\Pipeline;
use ElasticKit\DSL\Support\DeepClone;
use ElasticKit\DSL\Support\RegistersAgg;
use ElasticKit\DSL\Support\ResolvesProperties;
use stdClass;

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
    use DeepClone;
    use RegistersAgg;
    use ResolvesProperties;

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
    protected array $_subAggs = [];

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
    protected function node(Node $node): static
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
     * @param string $value
     * @return $this
     */
    public function alias(string $value): static
    {
        $this->_alias = $value;
        return $this;
    }

    /**
     * Get the alias name.
     *
     * @return string|null
     */
    public function getAlias(): ?string
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
     * @return static
     */
    public function aggs($alias, $aggs = null): static
    {
        return $this->registerAgg($alias, $aggs, $this->_subAggs);
    }

    /**
     * Serialize to an Elasticsearch aggregation DSL array.
     *
     * When $_alias is set, wraps the output under the alias name
     * (similar to Node's field wrapping).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->_properties !== null) {
            $resolved = $this->resolveProperties($this->_properties);
            if ($this->_alias !== null) {
                return [$this->_alias => ($resolved === [] ? new stdClass() : $resolved)];
            }
            return $resolved;
        }

        $inner = [];

        if ($this->_node instanceof Node) {
            $inner[$this->_node->key()] = $this->_node->toArray();
        }

        if (!empty($this->_subAggs)) {
            $inner['aggs'] = [];
            foreach ($this->_subAggs as $subAgg) {
                $inner['aggs'] += $subAgg->toArray();
            }
        }

        if ($this->_alias !== null) {
            return [$this->_alias => ($inner === [] ? new stdClass() : $inner)];
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
    public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION, int $depth = 512): string
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

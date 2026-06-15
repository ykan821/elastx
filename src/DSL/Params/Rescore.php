<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;
use stdClass;

/**
 * Rescores the top documents returned by a query using a secondary query.
 *
 * @phpstan-consistent-constructor
 */
class Rescore extends Node
{
    protected string $_key = 'rescore';

    /**
     * Number of documents to rescore per shard.
     *
     * @param int $size
     * @return static
     */
    public function windowSize(int $size): static
    {
        return $this->addProperty('window_size', $size);
    }

    /**
     * The query to use for rescoring.
     *
     * @param mixed $query
     * @return static
     */
    public function query($query): static
    {
        $this->_properties['query']['rescore_query'] = Query::create($query);
        return $this;
    }

    /**
     * Weight of the rescore query. Defaults to 1.0.
     *
     * @param float $weight
     * @return static
     */
    public function rescoreQueryWeight(float $weight): static
    {
        $this->_properties['query']['rescore_query_weight'] = $weight;
        return $this;
    }

    /**
     * Weight of the original query. Defaults to 1.0.
     *
     * @param float $weight
     * @return static
     */
    public function queryWeight(float $weight): static
    {
        $this->_properties['query']['query_weight'] = $weight;
        return $this;
    }

    /**
     * How scores are combined. Valid values: total,
     * multiply, max, avg. Defaults to total.
     *
     * @param string $mode
     * @return static
     */
    public function scoreMode(string $mode): static
    {
        $this->_properties['query']['score_mode'] = $mode;
        return $this;
    }

    public function toArray()
    {
        $result = parent::toArray();
        if (isset($result['query']['rescore_query']) && $result['query']['rescore_query'] instanceof Query) {
            $result['query']['rescore_query'] = $result['query']['rescore_query']->toArray()['query'] ?? new stdClass();
        }
        return $result;
    }
}

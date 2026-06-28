<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

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
     * @param int $value
     * @return static
     */
    public function windowSize(int $value): static
    {
        return $this->addProperty('window_size', $value);
    }

    /**
     * The query to use for rescoring.
     *
     * @param mixed $value
     * @return static
     */
    public function query($value): static
    {
        $this->_properties['query']['rescore_query'] = Query::create($value);
        return $this;
    }

    /**
     * Weight of the rescore query. Defaults to 1.0.
     *
     * @param float $value
     * @return static
     */
    public function rescoreQueryWeight(float $value): static
    {
        $this->_properties['query']['rescore_query_weight'] = $value;
        return $this;
    }

    /**
     * Weight of the original query. Defaults to 1.0.
     *
     * @param float $value
     * @return static
     */
    public function queryWeight(float $value): static
    {
        $this->_properties['query']['query_weight'] = $value;
        return $this;
    }

    /**
     * How scores are combined. Valid values: total,
     * multiply, max, avg. Defaults to total.
     *
     * @param string $value
     * @return static
     */
    public function scoreMode(string $value): static
    {
        $this->_properties['query']['score_mode'] = $value;
        return $this;
    }
}

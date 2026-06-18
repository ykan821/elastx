<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Pipeline;

use ElasticKit\DSL\Node;

/**
 * A pipeline aggregation that calculates the cumulative sum of a specified metric in a parent histogram.
 */
class CumulativeSum extends Node
{
    protected string $_key = 'cumulative_sum';

    /**
     * Path to the buckets to cumulatively sum.
     *
     * @param string $value
     * @return static
     */
    public function bucketsPath(string $value): static
    {
        return $this->addProperty('buckets_path', $value);
    }

    /**
     * Format for the output value.
     *
     * @param string $value
     * @return static
     */
    public function format(string $value): static
    {
        return $this->addProperty('format', $value);
    }
}

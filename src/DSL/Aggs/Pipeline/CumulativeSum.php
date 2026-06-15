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
     * @param string $path
     * @return static
     */
    public function bucketsPath(string $path): static
    {
        return $this->addProperty('buckets_path', $path);
    }

    /**
     * Format for the output value.
     *
     * @param string $format
     * @return static
     */
    public function format(string $format): static
    {
        return $this->addProperty('format', $format);
    }
}

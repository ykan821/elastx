<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Pipeline;

use ElasticKit\DSL\Node;

/**
 * A pipeline aggregation that calculates the derivative of a specified metric in a parent histogram.
 */
class Derivative extends Node
{
    protected string $_key = 'derivative';

    /**
     * Path to the buckets to differentiate.
     *
     * @param string $path
     * @return static
     */
    public function bucketsPath(string $path): static
    {
        return $this->addProperty('buckets_path', $path);
    }

    /**
     * Policy to apply when gaps are found in the data.
     *
     * @param string $policy
     * @return static
     */
    public function gapPolicy(string $policy): static
    {
        return $this->addProperty('gap_policy', $policy);
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

    /**
     * The unit for the derivative when the histogram uses time units.
     *
     * @param string $unit
     * @return static
     */
    public function unit(string $unit): static
    {
        return $this->addProperty('unit', $unit);
    }
}

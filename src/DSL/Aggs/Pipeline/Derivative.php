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
     * @param string $value
     * @return static
     */
    public function bucketsPath(string $value): static
    {
        return $this->addProperty('buckets_path', $value);
    }

    /**
     * Policy to apply when gaps are found in the data.
     *
     * @param string $value
     * @return static
     */
    public function gapPolicy(string $value): static
    {
        return $this->addProperty('gap_policy', $value);
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

    /**
     * The unit for the derivative when the histogram uses time units.
     *
     * @param string $value
     * @return static
     */
    public function unit(string $value): static
    {
        return $this->addProperty('unit', $value);
    }
}

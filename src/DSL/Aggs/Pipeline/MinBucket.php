<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Pipeline;

use ElasticKit\DSL\Node;

/**
 * A pipeline aggregation that identifies the bucket with the minimum value of a specified metric.
 */
class MinBucket extends Node
{
    protected string $_key = 'min_bucket';

    /**
     * Path to the buckets to find the minimum.
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
     * The value to use when the aggregation is missing a value.
     *
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }
}

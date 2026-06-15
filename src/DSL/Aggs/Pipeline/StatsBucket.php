<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Pipeline;

use ElasticKit\DSL\Node;

/**
 * A pipeline aggregation that computes stats over a specified metric in a sibling aggregation.
 */
class StatsBucket extends Node
{
    protected string $_key = 'stats_bucket';

    /**
     * Path to the buckets to compute stats on.
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
     * The value to use when the aggregation is missing a value.
     *
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }
}

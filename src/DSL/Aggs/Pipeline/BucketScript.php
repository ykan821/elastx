<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Pipeline;

use ElasticKit\DSL\Node;

/**
 * A pipeline aggregation that executes a script to perform per-bucket computations.
 */
class BucketScript extends Node
{
    protected string $_key = 'bucket_script';

    /**
     * Path to the buckets to use in the script.
     *
     * @param mixed $value
     * @return static
     */
    public function bucketsPath($value): static
    {
        return $this->addProperty('buckets_path', $value);
    }

    /**
     * The script to execute for each bucket.
     *
     * @param string|callable $value
     * @return static
     */
    public function script($value): static
    {
        return $this->addProperty('script', $value);
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
}

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
     * @param mixed $path
     * @return static
     */
    public function bucketsPath($path): static
    {
        return $this->addProperty('buckets_path', $path);
    }

    /**
     * The script to execute for each bucket.
     *
     * @param string|callable $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', $script);
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
}

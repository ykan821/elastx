<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into numeric value ranges.
 */
class Range extends Node
{
    protected string $_key = 'range';

    /**
     * Array of range definitions for bucketing.
     *
     * @param array<string, mixed> $ranges
     * @return static
     */
    public function ranges(array $ranges): static
    {
        return $this->addProperty('ranges', $ranges);
    }

    /**
     * Whether to return range buckets as a hash keyed by range key.
     *
     * @param bool $keyed
     * @return static
     */
    public function keyed(bool $keyed): static
    {
        return $this->addProperty('keyed', $keyed);
    }

    /**
     * Script to compute the bucket value.
     *
     * @param string|callable $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', $script);
    }

    /**
     * Value to use for documents missing the field value.
     *
     * @param float $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }
}

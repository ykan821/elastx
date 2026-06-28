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
     * @param array<string, mixed> $value
     * @return static
     */
    public function ranges(array $value): static
    {
        return $this->addProperty('ranges', $value);
    }

    /**
     * Whether to return range buckets as a hash keyed by range key.
     *
     * @param bool $value
     * @return static
     */
    public function keyed(bool $value): static
    {
        return $this->addProperty('keyed', $value);
    }

    /**
     * Script to compute the bucket value.
     *
     * @param string|callable $value
     * @return static
     */
    public function script($value): static
    {
        return $this->addProperty('script', $value);
    }

    /**
     * Value to use for documents missing the field value.
     *
     * @param float $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }
}

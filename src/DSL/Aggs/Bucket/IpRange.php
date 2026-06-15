<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into IP address ranges.
 */
class IpRange extends Node
{
    protected string $_key = 'ip_range';

    /**
     * Array of IP range definitions for bucketing.
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
     * Value to use for documents missing the field value.
     *
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }
}

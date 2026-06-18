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
     * Value to use for documents missing the field value.
     *
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }
}

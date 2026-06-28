<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into buckets based on date ranges.
 */
class DateRange extends Node
{
    protected string $_key = 'date_range';

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
     * Date format pattern for range keys.
     *
     * @param string $value
     * @return static
     */
    public function format(string $value): static
    {
        return $this->addProperty('format', $value);
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

    /**
     * Time zone for date calculations.
     *
     * @param string $value
     * @return static
     */
    public function timeZone(string $value): static
    {
        return $this->addProperty('time_zone', $value);
    }
}

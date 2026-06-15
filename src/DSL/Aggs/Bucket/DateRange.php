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
     * Date format pattern for range keys.
     *
     * @param string $format
     * @return static
     */
    public function format(string $format): static
    {
        return $this->addProperty('format', $format);
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

    /**
     * Time zone for date calculations.
     *
     * @param string $timeZone
     * @return static
     */
    public function timeZone(string $timeZone): static
    {
        return $this->addProperty('time_zone', $timeZone);
    }
}

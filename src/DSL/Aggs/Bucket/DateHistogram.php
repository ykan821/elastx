<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into buckets based on date intervals.
 */
class DateHistogram extends Node
{
    protected string $_key = 'date_histogram';

    /**
     * Calendar-aware interval for bucketing (e.g. month, week).
     *
     * @param string $value
     * @return static
     */
    public function calendarInterval(string $value): static
    {
        return $this->addProperty('calendar_interval', $value);
    }

    /**
     * Interval for bucketing. Deprecated in favor of calendar_interval or fixed_interval.
     *
     * @deprecated ES deprecated the bare `interval` key; use calendarInterval() or fixedInterval() instead.
     * @param string $value
     * @return static
     */
    public function interval(string $value): static
    {
        return $this->addProperty('interval', $value);
    }

    /**
     * Fixed-unit interval for bucketing (e.g. 30d, 12h).
     *
     * @param string $value
     * @return static
     */
    public function fixedInterval(string $value): static
    {
        return $this->addProperty('fixed_interval', $value);
    }

    /**
     * Date format pattern for bucket keys.
     *
     * @param string $value
     * @return static
     */
    public function format(string $value): static
    {
        return $this->addProperty('format', $value);
    }

    /**
     * Time zone for bucketing.
     *
     * @param string $value
     * @return static
     */
    public function timeZone(string $value): static
    {
        return $this->addProperty('time_zone', $value);
    }

    /**
     * Minimum number of documents in a bucket to be returned.
     *
     * @param int $value
     * @return static
     */
    public function minDocCount(int $value): static
    {
        return $this->addProperty('min_doc_count', $value);
    }

    /**
     * Extends the bucket range beyond the data bounds.
     *
     * @param mixed $value
     * @return static
     */
    public function extendedBounds($value): static
    {
        return $this->addProperty('extended_bounds', $value);
    }

    /**
     * Limits the bucket range to a bounded range.
     *
     * @param mixed $value
     * @return static
     */
    public function hardBounds($value): static
    {
        return $this->addProperty('hard_bounds', $value);
    }

    /**
     * Sort order for buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function order($value): static
    {
        return $this->addProperty('order', $value);
    }

    /**
     * Whether to return bucket keys as strings.
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

    /**
     * Offset for each bucket start time.
     *
     * @param string $value
     * @return static
     */
    public function offset(string $value): static
    {
        return $this->addProperty('offset', $value);
    }
}

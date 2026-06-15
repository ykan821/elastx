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
     * @param string $calendarInterval
     * @return static
     */
    public function calendarInterval(string $calendarInterval): static
    {
        return $this->addProperty('calendar_interval', $calendarInterval);
    }

    /**
     * Interval for bucketing. Deprecated in favor of calendar_interval or fixed_interval.
     *
     * @param string $interval
     * @return static
     */
    public function interval(string $interval): static
    {
        return $this->addProperty('interval', $interval);
    }

    /**
     * Fixed-unit interval for bucketing (e.g. 30d, 12h).
     *
     * @param string $fixedInterval
     * @return static
     */
    public function fixedInterval(string $fixedInterval): static
    {
        return $this->addProperty('fixed_interval', $fixedInterval);
    }

    /**
     * Date format pattern for bucket keys.
     *
     * @param string $format
     * @return static
     */
    public function format(string $format): static
    {
        return $this->addProperty('format', $format);
    }

    /**
     * Time zone for bucketing.
     *
     * @param string $timeZone
     * @return static
     */
    public function timeZone(string $timeZone): static
    {
        return $this->addProperty('time_zone', $timeZone);
    }

    /**
     * Minimum number of documents in a bucket to be returned.
     *
     * @param int $minDocCount
     * @return static
     */
    public function minDocCount(int $minDocCount): static
    {
        return $this->addProperty('min_doc_count', $minDocCount);
    }

    /**
     * Extends the bucket range beyond the data bounds.
     *
     * @param mixed $extendedBounds
     * @return static
     */
    public function extendedBounds($extendedBounds): static
    {
        return $this->addProperty('extended_bounds', $extendedBounds);
    }

    /**
     * Limits the bucket range to a bounded range.
     *
     * @param mixed $hardBounds
     * @return static
     */
    public function hardBounds($hardBounds): static
    {
        return $this->addProperty('hard_bounds', $hardBounds);
    }

    /**
     * Sort order for buckets.
     *
     * @param mixed $order
     * @return static
     */
    public function order($order): static
    {
        return $this->addProperty('order', $order);
    }

    /**
     * Whether to return bucket keys as strings.
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

    /**
     * Offset for each bucket start time.
     *
     * @param string $offset
     * @return static
     */
    public function offset(string $offset): static
    {
        return $this->addProperty('offset', $offset);
    }
}

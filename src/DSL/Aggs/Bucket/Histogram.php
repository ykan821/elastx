<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into numeric value intervals.
 */
class Histogram extends Node
{
    protected string $_key = 'histogram';

    /**
     * Interval size for each bucket.
     *
     * @param float $interval
     * @return static
     */
    public function interval(float $interval): static
    {
        return $this->addProperty('interval', $interval);
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
     * @param mixed $bounds
     * @return static
     */
    public function extendedBounds($bounds): static
    {
        return $this->addProperty('extended_bounds', $bounds);
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
     * @param float $missing
     * @return static
     */
    public function missing(float $missing): static
    {
        return $this->addProperty('missing', $missing);
    }

    /**
     * Format pattern for bucket key values.
     *
     * @param string $format
     * @return static
     */
    public function format(string $format): static
    {
        return $this->addProperty('format', $format);
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
     * Offset for bucket starting values.
     *
     * @param float $offset
     * @return static
     */
    public function offset(float $offset): static
    {
        return $this->addProperty('offset', $offset);
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
}

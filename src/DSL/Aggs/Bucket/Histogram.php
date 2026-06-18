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
     * @param float $value
     * @return static
     */
    public function interval(float $value): static
    {
        return $this->addProperty('interval', $value);
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
     * @param float $value
     * @return static
     */
    public function missing(float $value): static
    {
        return $this->addProperty('missing', $value);
    }

    /**
     * Format pattern for bucket key values.
     *
     * @param string $value
     * @return static
     */
    public function format(string $value): static
    {
        return $this->addProperty('format', $value);
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
     * Offset for bucket starting values.
     *
     * @param float $value
     * @return static
     */
    public function offset(float $value): static
    {
        return $this->addProperty('offset', $value);
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
}

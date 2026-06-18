<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that automatically determines bucket intervals for date values.
 */
class AutoDateHistogram extends Node
{
    protected string $_key = 'auto_date_histogram';

    /**
     * Target number of buckets to return.
     *
     * @param int $value
     * @return static
     */
    public function buckets(int $value): static
    {
        return $this->addProperty('buckets', $value);
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
     * Minimum interval to use when automatically determining buckets.
     *
     * @param string $value
     * @return static
     */
    public function minimumInterval(string $value): static
    {
        return $this->addProperty('minimum_interval', $value);
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

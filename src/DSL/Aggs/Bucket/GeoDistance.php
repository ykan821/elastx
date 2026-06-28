<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into buckets based on distance from a geo point.
 */
class GeoDistance extends Node
{
    protected string $_key = 'geo_distance';

    /**
     * The central geo point from which distances are measured.
     *
     * @param mixed $value
     * @return static
     */
    public function origin($value): static
    {
        return $this->addProperty('origin', $value);
    }

    /**
     * Distance unit (e.g. km, mi, m).
     *
     * @param string $value
     * @return static
     */
    public function unit(string $value): static
    {
        return $this->addProperty('unit', $value);
    }

    /**
     * How to compute the distance (arc or plane).
     *
     * @param string $value
     * @return static
     */
    public function distanceType(string $value): static
    {
        return $this->addProperty('distance_type', $value);
    }

    /**
     * Array of distance range definitions for bucketing.
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
}

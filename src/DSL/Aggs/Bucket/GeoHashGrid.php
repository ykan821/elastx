<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into geohash grid cells.
 */
class GeoHashGrid extends Node
{
    protected string $_key = 'geohash_grid';

    /**
     * Geohash precision (length) for grid cells.
     *
     * @param int $value
     * @return static
     */
    public function precision(int $value): static
    {
        return $this->addProperty('precision', $value);
    }

    /**
     * Maximum number of geohash buckets to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * Number of geohash buckets to return from each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }
}

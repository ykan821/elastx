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
     * @param int $precision
     * @return static
     */
    public function precision(int $precision): static
    {
        return $this->addProperty('precision', $precision);
    }

    /**
     * Maximum number of geohash buckets to return.
     *
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }

    /**
     * Number of geohash buckets to return from each shard.
     *
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }
}

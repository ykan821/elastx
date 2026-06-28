<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents into H3 hexagonal grid cells.
 */
class GeohexGrid extends Node
{
    protected string $_key = 'geohex_grid';

    /**
     * H3 resolution for grid cells.
     *
     * @param int $value
     * @return static
     */
    public function precision(int $value): static
    {
        return $this->addProperty('precision', $value);
    }

    /**
     * Maximum number of hex buckets to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * Number of hex buckets to return from each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class VariableWidthHistogram extends Node
{
    protected string $_key = 'variable_width_histogram';

    /**
     * @param int $buckets
     * @return static
     */
    public function buckets(int $buckets): static
    {
        return $this->addProperty('buckets', $buckets);
    }

    /**
     * @param int $shardBuckets
     * @return static
     */
    public function shardBuckets(int $shardBuckets): static
    {
        return $this->addProperty('shard_buckets', $shardBuckets);
    }
}

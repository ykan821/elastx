<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class VariableWidthHistogram extends Node
{
    protected string $_key = 'variable_width_histogram';

    /**
     * @param int $value
     * @return static
     */
    public function buckets(int $value): static
    {
        return $this->addProperty('buckets', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function shardBuckets(int $value): static
    {
        return $this->addProperty('shard_buckets', $value);
    }
}

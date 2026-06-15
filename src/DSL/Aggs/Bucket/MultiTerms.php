<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class MultiTerms extends Node
{
    protected string $_key = 'multi_terms';

    /**
     * @param mixed $terms
     * @return static
     */
    public function terms($terms): static
    {
        return $this->addProperty('terms', $terms, true);
    }

    /**
     * @param mixed $order
     * @return static
     */
    public function order($order): static
    {
        return $this->addProperty('order', $order);
    }

    /**
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }

    /**
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }

    /**
     * @param int $minDocCount
     * @return static
     */
    public function minDocCount(int $minDocCount): static
    {
        return $this->addProperty('min_doc_count', $minDocCount);
    }

    /**
     * @param int $shardMinDocCount
     * @return static
     */
    public function shardMinDocCount(int $shardMinDocCount): static
    {
        return $this->addProperty('shard_min_doc_count', $shardMinDocCount);
    }

    /**
     * @param string $collectMode
     * @return static
     */
    public function collectMode(string $collectMode): static
    {
        return $this->addProperty('collect_mode', $collectMode);
    }
}

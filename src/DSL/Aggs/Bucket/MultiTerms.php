<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class MultiTerms extends Node
{
    protected string $_key = 'multi_terms';

    /**
     * @param mixed $value
     * @return static
     */
    public function terms($value): static
    {
        return $this->addProperty('terms', $value, true);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function order($value): static
    {
        return $this->addProperty('order', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function minDocCount(int $value): static
    {
        return $this->addProperty('min_doc_count', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function shardMinDocCount(int $value): static
    {
        return $this->addProperty('shard_min_doc_count', $value);
    }

    /**
     * @param string $value
     * @return static
     */
    public function collectMode(string $value): static
    {
        return $this->addProperty('collect_mode', $value);
    }
}

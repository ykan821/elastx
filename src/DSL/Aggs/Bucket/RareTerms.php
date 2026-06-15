<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class RareTerms extends Node
{
    protected string $_key = 'rare_terms';

    /**
     * @param int $maxDocCount
     * @return static
     */
    public function maxDocCount(int $maxDocCount): static
    {
        return $this->addProperty('max_doc_count', $maxDocCount);
    }

    /**
     * @param mixed $precision
     * @return static
     */
    public function precision($precision): static
    {
        return $this->addProperty('precision', $precision);
    }

    /**
     * @param mixed $include
     * @return static
     */
    public function include($include): static
    {
        return $this->addProperty('include', $include);
    }

    /**
     * @param mixed $exclude
     * @return static
     */
    public function exclude($exclude): static
    {
        return $this->addProperty('exclude', $exclude);
    }

    /**
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }

    /**
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }
}

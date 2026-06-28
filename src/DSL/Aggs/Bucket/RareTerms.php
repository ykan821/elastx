<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class RareTerms extends Node
{
    protected string $_key = 'rare_terms';

    /**
     * @param int $value
     * @return static
     */
    public function maxDocCount(int $value): static
    {
        return $this->addProperty('max_doc_count', $value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function precision($value): static
    {
        return $this->addProperty('precision', $value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function include($value): static
    {
        return $this->addProperty('include', $value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function exclude($value): static
    {
        return $this->addProperty('exclude', $value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }
}

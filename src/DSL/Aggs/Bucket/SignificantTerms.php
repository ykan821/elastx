<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that returns interesting or unusual occurrences of terms in a field.
 */
class SignificantTerms extends Node
{
    protected string $_key = 'significant_terms';

    /**
     * Maximum number of significant terms to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * Number of candidate terms to return from each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }

    /**
     * Minimum document count for a term to be returned.
     *
     * @param int $value
     * @return static
     */
    public function minDocCount(int $value): static
    {
        return $this->addProperty('min_doc_count', $value);
    }

    /**
     * Minimum document count for a term to be considered on each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardMinDocCount(int $value): static
    {
        return $this->addProperty('shard_min_doc_count', $value);
    }

    /**
     * Terms to include in the aggregation.
     *
     * @param mixed $value
     * @return static
     */
    public function include($value): static
    {
        return $this->addProperty('include', $value);
    }

    /**
     * Terms to exclude from the aggregation.
     *
     * @param mixed $value
     * @return static
     */
    public function exclude($value): static
    {
        return $this->addProperty('exclude', $value);
    }

    /**
     * Query to filter the background document set for significance calculation.
     *
     * @param mixed $value
     * @return static
     */
    public function backgroundFilter($value): static
    {
        return $this->addProperty('background_filter', $value);
    }

    /**
     * Execution hint for the aggregation mechanism.
     *
     * @param string $value
     * @return static
     */
    public function executionHint(string $value): static
    {
        return $this->addProperty('execution_hint', $value);
    }
}

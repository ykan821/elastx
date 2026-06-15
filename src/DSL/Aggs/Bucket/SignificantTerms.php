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
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }

    /**
     * Number of candidate terms to return from each shard.
     *
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }

    /**
     * Minimum document count for a term to be returned.
     *
     * @param int $minDocCount
     * @return static
     */
    public function minDocCount(int $minDocCount): static
    {
        return $this->addProperty('min_doc_count', $minDocCount);
    }

    /**
     * Minimum document count for a term to be considered on each shard.
     *
     * @param int $shardMinDocCount
     * @return static
     */
    public function shardMinDocCount(int $shardMinDocCount): static
    {
        return $this->addProperty('shard_min_doc_count', $shardMinDocCount);
    }

    /**
     * Terms to include in the aggregation.
     *
     * @param mixed $include
     * @return static
     */
    public function include($include): static
    {
        return $this->addProperty('include', $include);
    }

    /**
     * Terms to exclude from the aggregation.
     *
     * @param mixed $exclude
     * @return static
     */
    public function exclude($exclude): static
    {
        return $this->addProperty('exclude', $exclude);
    }

    /**
     * Query to filter the background document set for significance calculation.
     *
     * @param mixed $backgroundFilter
     * @return static
     */
    public function backgroundFilter($backgroundFilter): static
    {
        return $this->addProperty('background_filter', $backgroundFilter);
    }

    /**
     * Execution hint for the aggregation mechanism.
     *
     * @param string $executionHint
     * @return static
     */
    public function executionHint(string $executionHint): static
    {
        return $this->addProperty('execution_hint', $executionHint);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that returns significant text occurrences, similar to significant_terms but for analyzed text.
 */
class SignificantText extends Node
{
    protected string $_key = 'significant_text';

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
     * Whether to filter duplicate text before analysis.
     *
     * @param bool $value
     * @return static
     */
    public function filterDuplicateText(bool $value): static
    {
        return $this->addProperty('filter_duplicate_text', $value);
    }
}

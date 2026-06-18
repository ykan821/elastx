<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that categorizes text fields into buckets based on similarity.
 */
class CategorizeText extends Node
{
    protected string $_key = 'categorize_text';

    /**
     * Analyzer used for categorization.
     *
     * @param mixed $value
     * @return static
     */
    public function categorizationAnalyzer($value): static
    {
        return $this->addProperty('categorization_analyzer', $value);
    }

    /**
     * Filters applied to each token before categorization.
     *
     * @param array<string, mixed> $value
     * @return static
     */
    public function categorizationFilters(array $value): static
    {
        return $this->addProperty('categorization_filters', $value);
    }

    /**
     * Maximum number of matched tokens to consider.
     *
     * @param int $value
     * @return static
     */
    public function maxMatchedTokens(int $value): static
    {
        return $this->addProperty('max_matched_tokens', $value);
    }

    /**
     * Maximum number of unique tokens to consider.
     *
     * @param int $value
     * @return static
     */
    public function maxUniqueTokens(int $value): static
    {
        return $this->addProperty('max_unique_tokens', $value);
    }

    /**
     * Minimum document count per bucket.
     *
     * @param int $value
     * @return static
     */
    public function minDocCount(int $value): static
    {
        return $this->addProperty('min_doc_count', $value);
    }

    /**
     * Minimum document count per shard.
     *
     * @param int $value
     * @return static
     */
    public function shardMinDocCount(int $value): static
    {
        return $this->addProperty('shard_min_doc_count', $value);
    }

    /**
     * Number of categories to return from each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }

    /**
     * Similarity threshold for grouping categories.
     *
     * @param float $value
     * @return static
     */
    public function similarityThreshold(float $value): static
    {
        return $this->addProperty('similarity_threshold', $value);
    }

    /**
     * Maximum number of categories to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }
}

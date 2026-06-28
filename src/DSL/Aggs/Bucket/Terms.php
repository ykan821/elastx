<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that groups documents by a specified field.
 */
class Terms extends Node
{
    protected string $_key = 'terms';

    /**
     * Maximum number of term buckets to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * Sort order for term buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function order($value): static
    {
        return $this->addProperty('order', $value);
    }

    /**
     * Minimum document count for a term bucket to be returned.
     *
     * @param int $value
     * @return static
     */
    public function minDocCount(int $value): static
    {
        return $this->addProperty('min_doc_count', $value);
    }

    /**
     * Number of term buckets to return from each shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }

    /**
     * Whether to show document count error for each term.
     *
     * @param bool $value
     * @return static
     */
    public function showTermDocCountError(bool $value): static
    {
        return $this->addProperty('show_term_doc_count_error', $value);
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
     * Value to use for documents missing the field value.
     *
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }

    /**
     * Collection mode for the aggregation (breadth_first or depth_first).
     *
     * @param string $value
     * @return static
     */
    public function collectMode(string $value): static
    {
        return $this->addProperty('collect_mode', $value);
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

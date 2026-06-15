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
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }

    /**
     * Sort order for term buckets.
     *
     * @param mixed $order
     * @return static
     */
    public function order($order): static
    {
        return $this->addProperty('order', $order);
    }

    /**
     * Minimum document count for a term bucket to be returned.
     *
     * @param int $minDocCount
     * @return static
     */
    public function minDocCount(int $minDocCount): static
    {
        return $this->addProperty('min_doc_count', $minDocCount);
    }

    /**
     * Number of term buckets to return from each shard.
     *
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }

    /**
     * Whether to show document count error for each term.
     *
     * @param bool $show
     * @return static
     */
    public function showTermDocCountError(bool $show): static
    {
        return $this->addProperty('show_term_doc_count_error', $show);
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
     * Value to use for documents missing the field value.
     *
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }

    /**
     * Collection mode for the aggregation (breadth_first or depth_first).
     *
     * @param string $collectMode
     * @return static
     */
    public function collectMode(string $collectMode): static
    {
        return $this->addProperty('collect_mode', $collectMode);
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

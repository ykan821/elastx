<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that limits any child aggregations to a diversified sample of top-scoring documents.
 */
class DiversifiedSampler extends Node
{
    protected string $_key = 'diversified_sampler';

    /**
     * Number of documents to sample per shard.
     *
     * @param int $value
     * @return static
     */
    public function shardSize(int $value): static
    {
        return $this->addProperty('shard_size', $value);
    }

    /**
     * Maximum number of documents per unique value.
     *
     * @param int $value
     * @return static
     */
    public function maxDocsPerValue(int $value): static
    {
        return $this->addProperty('max_docs_per_value', $value);
    }

    /**
     * Execution hint for the aggregation.
     *
     * @param string $value
     * @return static
     */
    public function executionHint(string $value): static
    {
        return $this->addProperty('execution_hint', $value);
    }
}

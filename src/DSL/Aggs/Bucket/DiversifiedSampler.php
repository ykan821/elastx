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
     * @param int $shardSize
     * @return static
     */
    public function shardSize(int $shardSize): static
    {
        return $this->addProperty('shard_size', $shardSize);
    }

    /**
     * Maximum number of documents per unique value.
     *
     * @param int $maxDocsPerValue
     * @return static
     */
    public function maxDocsPerValue(int $maxDocsPerValue): static
    {
        return $this->addProperty('max_docs_per_value', $maxDocsPerValue);
    }

    /**
     * Execution hint for the aggregation.
     *
     * @param string $executionHint
     * @return static
     */
    public function executionHint(string $executionHint): static
    {
        return $this->addProperty('execution_hint', $executionHint);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that defines buckets based on filter queries.
 */
class Filters extends Node
{
    protected string $_key = 'filters';

    /**
     * Filter queries used to create buckets.
     *
     * @param mixed $filters
     * @return static
     */
    public function filters($filters): static
    {
        return $this->addProperty('filters', $filters);
    }

    /**
     * Key for the bucket that holds documents not matching any filter.
     *
     * @param string $otherBucketKey
     * @return static
     */
    public function otherBucketKey(string $otherBucketKey): static
    {
        return $this->addProperty('other_bucket_key', $otherBucketKey);
    }

    /**
     * Whether to return buckets as a hash keyed by filter name.
     *
     * @param bool $keyed
     * @return static
     */
    public function keyed(bool $keyed): static
    {
        return $this->addProperty('keyed', $keyed);
    }
}

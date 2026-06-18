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
     * @param mixed $value
     * @return static
     */
    public function filters($value): static
    {
        return $this->addProperty('filters', $value);
    }

    /**
     * Key for the bucket that holds documents not matching any filter.
     *
     * @param string $value
     * @return static
     */
    public function otherBucketKey(string $value): static
    {
        return $this->addProperty('other_bucket_key', $value);
    }

    /**
     * Whether to return buckets as a hash keyed by filter name.
     *
     * @param bool $value
     * @return static
     */
    public function keyed(bool $value): static
    {
        return $this->addProperty('keyed', $value);
    }
}

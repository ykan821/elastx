<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that creates composite buckets from multiple sources.
 */
class Composite extends Node
{
    protected string $_key = 'composite';

    /**
     * List of source definitions used to build composite buckets.
     *
     * @param mixed $sources
     * @return static
     */
    public function sources($sources): static
    {
        return $this->addProperty('sources', $sources);
    }

    /**
     * Cursor value to resume pagination after a previous composite response.
     *
     * @param mixed $after
     * @return static
     */
    public function after($after): static
    {
        return $this->addProperty('after', $after);
    }

    /**
     * Sort order for composite buckets.
     *
     * @param mixed $order
     * @return static
     */
    public function order($order): static
    {
        return $this->addProperty('order', $order);
    }

    /**
     * Maximum number of composite buckets to return.
     *
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }
}

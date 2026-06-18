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
     * @param mixed $value
     * @return static
     */
    public function sources($value): static
    {
        return $this->addProperty('sources', $value);
    }

    /**
     * Cursor value to resume pagination after a previous composite response.
     *
     * @param mixed $value
     * @return static
     */
    public function after($value): static
    {
        return $this->addProperty('after', $value);
    }

    /**
     * Sort order for composite buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function order($value): static
    {
        return $this->addProperty('order', $value);
    }

    /**
     * Maximum number of composite buckets to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }
}

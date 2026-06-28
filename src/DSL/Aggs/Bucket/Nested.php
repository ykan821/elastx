<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that aggregates on nested document fields.
 */
class Nested extends Node
{
    protected string $_key = 'nested';

    /**
     * Path to the nested object to aggregate on.
     *
     * @param string $value
     * @return static
     */
    public function path(string $value): static
    {
        return $this->addProperty('path', $value);
    }

    /**
     * Whether to return an empty bucket instead of an error for unmapped nested types.
     *
     * @param bool $value
     * @return static
     */
    public function ignoreUnmapped(bool $value): static
    {
        return $this->addProperty('ignore_unmapped', $value);
    }
}

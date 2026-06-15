<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that creates a bucket for documents missing a field value.
 */
class Missing extends Node
{
    protected string $_key = 'missing';

    /**
     * Value to treat as missing for the field.
     *
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }
}

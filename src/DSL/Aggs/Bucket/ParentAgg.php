<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that aggregates on parent documents from a join field.
 */
class ParentAgg extends Node
{
    protected string $_key = 'parent';

    /**
     * The child type that identifies the parent documents to aggregate on.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->addProperty('type', $type);
    }
}

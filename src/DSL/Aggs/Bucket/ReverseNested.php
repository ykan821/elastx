<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that aggregates on parent documents from within a nested aggregation.
 */
class ReverseNested extends Node
{
    protected string $_key = 'reverse_nested';

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (empty($this->_properties)) {
            return (object)[];
        }
        return parent::toArray();
    }

    /**
     * Path to the nested object to reverse out of.
     *
     * @param string $value
     * @return static
     */
    public function path(string $value): static
    {
        return $this->addProperty('path', $value);
    }
}

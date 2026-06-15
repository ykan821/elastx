<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Allows using span queries across multiple fields by masking one field as another.
 */
class SpanFieldMasking extends Node
{
    protected string $_key = 'span_field_masking';

    /**
     * The inner span query to execute.
     *
     * @param mixed $query
     * @return static
     */
    public function query($query): static
    {
        return $this->addProperty('query', Query::create($query));
    }
}

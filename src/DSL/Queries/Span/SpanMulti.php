<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Wraps a non-span query such as term, range, or wildcard so it can be used in span queries.
 */
class SpanMulti extends Node
{
    protected string $_key = 'span_multi';

    /**
     * The non-span query to wrap as a span query.
     *
     * @param mixed $match
     * @return static
     */
    public function match($match): static
    {
        return $this->addProperty('match', Query::create($match));
    }
}

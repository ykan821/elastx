<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Matches spans whose matches are within another span query's matches.
 */
class SpanWithin extends Node
{
    protected string $_key = 'span_within';

    /**
     * The little span query whose matches must fall within the big span.
     *
     * @param mixed $value
     * @return static
     */
    public function little($value): static
    {
        return $this->addProperty('little', Query::create($value));
    }

    /**
     * The big span query that must contain matches from the little span.
     *
     * @param mixed $value
     * @return static
     */
    public function big($value): static
    {
        return $this->addProperty('big', Query::create($value));
    }
}

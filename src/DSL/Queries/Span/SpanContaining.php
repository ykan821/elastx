<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Matches spans that contain another span query's matches.
 */
class SpanContaining extends Node
{
    protected string $_key = 'span_containing';

    /**
     * The little span query whose matches must be contained within the big span.
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

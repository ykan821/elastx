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
     * @param mixed $little
     * @return static
     */
    public function little($little): static
    {
        return $this->addProperty('little', Query::create($little));
    }

    /**
     * The big span query that must contain matches from the little span.
     *
     * @param mixed $big
     * @return static
     */
    public function big($big): static
    {
        return $this->addProperty('big', Query::create($big));
    }
}

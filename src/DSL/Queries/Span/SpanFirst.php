<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Matches spans near the beginning of a field, restricting matches to the first N positions.
 */
class SpanFirst extends Node
{
    protected string $_key = 'span_first';

    /**
     * The inner span query whose matches are restricted.
     *
     * @param mixed $match
     * @return static
     */
    public function match($match): static
    {
        return $this->addProperty('match', Query::create($match));
    }

    /**
     * The maximum end position permitted for the span match.
     *
     * @param int $end
     * @return static
     */
    public function end(int $end): static
    {
        return $this->addProperty('end', $end);
    }
}

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
     * @param mixed $value
     * @return static
     */
    public function match($value): static
    {
        return $this->addProperty('match', Query::create($value));
    }

    /**
     * The maximum end position permitted for the span match.
     *
     * @param int $value
     * @return static
     */
    public function end(int $value): static
    {
        return $this->addProperty('end', $value);
    }
}

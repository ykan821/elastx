<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Support\ClausesSupport;
use ElasticKit\DSL\Node;

/**
 * Matches spans that are near each other, with configurable slop and ordering.
 */
class SpanNear extends Node
{
    use ClausesSupport;

    protected string $_key = 'span_near';

    /**
     * The list of span query clauses that must appear near each other.
     * Supports multiple calls to incrementally build.
     *
     * @param mixed $value
     * @return static
     */
    public function clauses($value): static
    {
        return $this->addClause('clauses', $value);
    }

    /**
     * The maximum number of positions allowed between matching spans.
     *
     * @param int $value
     * @return static
     */
    public function slop(int $value): static
    {
        return $this->addProperty('slop', $value);
    }

    /**
     * Whether the span clauses must appear in their specified order.
     *
     * @param bool $value
     * @return static
     */
    public function inOrder(bool $value): static
    {
        return $this->addProperty('in_order', $value);
    }
}

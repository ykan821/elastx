<?php

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Shared\ClausesSupport;
use ElasticKit\DSL\Node;

/**
 * Matches spans that are near each other, with configurable slop and ordering.
 */
class SpanNear extends Node
{
    use ClausesSupport;

    protected $_key = 'span_near';

    /**
     * The list of span query clauses that must appear near each other.
     * Supports multiple calls to incrementally build.
     *
     * @param mixed $clauses
     * @return static
     */
    public function clauses($clauses)
    {
        return $this->addClause('clauses', $clauses);
    }

    /**
     * The maximum number of positions allowed between matching spans.
     *
     * @param int $slop
     * @return static
     */
    public function slop($slop)
    {
        return $this->addProperty('slop', $slop);
    }

    /**
     * Whether the span clauses must appear in their specified order.
     *
     * @param bool $inOrder
     * @return static
     */
    public function inOrder($inOrder)
    {
        return $this->addProperty('in_order', $inOrder);
    }
}

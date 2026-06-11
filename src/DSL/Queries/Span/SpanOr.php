<?php

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Shared\ClausesSupport;
use ElasticKit\DSL\Node;

/**
 * Matches the union of multiple span queries, combining their results.
 */
class SpanOr extends Node
{
    use ClausesSupport;

    protected $_key = 'span_or';

    /**
     * The list of span query clauses to combine.
     * Supports multiple calls to incrementally build.
     *
     * @param mixed $clauses
     * @return static
     */
    public function clauses($clauses)
    {
        return $this->addClause('clauses', $clauses);
    }
}

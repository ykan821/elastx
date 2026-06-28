<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Support\ClausesSupport;
use ElasticKit\DSL\Node;

/**
 * Matches the union of multiple span queries, combining their results.
 */
class SpanOr extends Node
{
    use ClausesSupport;

    protected string $_key = 'span_or';

    /**
     * The list of span query clauses to combine.
     * Supports multiple calls to incrementally build.
     *
     * @param mixed $value
     * @return static
     */
    public function clauses($value): static
    {
        return $this->addClause('clauses', $value);
    }
}

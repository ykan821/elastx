<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Matches a single term as a span query, the simplest span query type.
 */
class SpanTerm extends Node
{
    protected string $_key = 'span_term';

    protected bool $_fieldKeyed = true;

    /**
     * The value of the term to match.
     *
     * ES span_term uses the {value} key (not {term}); delegates to value().
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function term(string|int|float|bool $value): static
    {
        return $this->value($value);
    }

    /**
     * The value of the term to match (alias for the field value).
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function value(string|int|float|bool $value): static
    {
        return $this->addProperty('value', $value);
    }
}

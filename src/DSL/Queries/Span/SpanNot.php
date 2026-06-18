<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Span;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Excludes matches of one span query that overlap with matches of another span query.
 */
class SpanNot extends Node
{
    protected string $_key = 'span_not';

    /**
     * The span query whose matches are included.
     *
     * @param mixed $value
     * @return static
     */
    public function include($value): static
    {
        return $this->addProperty('include', Query::create($value));
    }

    /**
     * The span query whose overlapping matches are excluded.
     *
     * @param mixed $value
     * @return static
     */
    public function exclude($value): static
    {
        return $this->addProperty('exclude', Query::create($value));
    }

    /**
     * The number of positions before the include span that must not overlap with the exclude span.
     *
     * @param int $value
     * @return static
     */
    public function pre(int $value): static
    {
        return $this->addProperty('pre', $value);
    }

    /**
     * The number of positions after the include span that must not overlap with the exclude span.
     *
     * @param int $value
     * @return static
     */
    public function post(int $value): static
    {
        return $this->addProperty('post', $value);
    }

    /**
     * The number of positions both before and after the include span that must not overlap with the exclude span.
     *
     * @param int $value
     * @return static
     */
    public function dist(int $value): static
    {
        return $this->addProperty('dist', $value);
    }
}

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
     * @param mixed $include
     * @return static
     */
    public function include($include): static
    {
        return $this->addProperty('include', Query::create($include));
    }

    /**
     * The span query whose overlapping matches are excluded.
     *
     * @param mixed $exclude
     * @return static
     */
    public function exclude($exclude): static
    {
        return $this->addProperty('exclude', Query::create($exclude));
    }

    /**
     * The number of positions before the include span that must not overlap with the exclude span.
     *
     * @param int $pre
     * @return static
     */
    public function pre(int $pre): static
    {
        return $this->addProperty('pre', $pre);
    }

    /**
     * The number of positions after the include span that must not overlap with the exclude span.
     *
     * @param int $post
     * @return static
     */
    public function post(int $post): static
    {
        return $this->addProperty('post', $post);
    }

    /**
     * The number of positions both before and after the include span that must not overlap with the exclude span.
     *
     * @param int $dist
     * @return static
     */
    public function dist(int $dist): static
    {
        return $this->addProperty('dist', $dist);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Span\SpanContaining;
use ElasticKit\DSL\Queries\Span\SpanFieldMasking;
use ElasticKit\DSL\Queries\Span\SpanFirst;
use ElasticKit\DSL\Queries\Span\SpanMulti;
use ElasticKit\DSL\Queries\Span\SpanNear;
use ElasticKit\DSL\Queries\Span\SpanNot;
use ElasticKit\DSL\Queries\Span\SpanOr;
use ElasticKit\DSL\Queries\Span\SpanTerm;
use ElasticKit\DSL\Queries\Span\SpanWithin;

/**
 * Shortcut methods for span query types.
 */
trait Span
{
    /**
     * Add a span_containing query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanContaining($value): static
    {
        return $this->addQuery(SpanContaining::create($value));
    }

    /**
     * Add a span_field_masking query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanFieldMasking($value): static
    {
        return $this->addQuery(SpanFieldMasking::create($value));
    }

    /**
     * Add a span_first query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanFirst($value): static
    {
        return $this->addQuery(SpanFirst::create($value));
    }

    /**
     * Add a span_multi query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanMulti($value): static
    {
        return $this->addQuery(SpanMulti::create($value));
    }

    /**
     * Add a span_near query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function spanNear($field = null, $value = null): static
    {
        return $this->addQuery(SpanNear::create($field, $value));
    }

    /**
     * Add a span_not query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanNot($value): static
    {
        return $this->addQuery(SpanNot::create($value));
    }

    /**
     * Add a span_or query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function spanOr($field = null, $value = null): static
    {
        return $this->addQuery(SpanOr::create($field, $value));
    }

    /**
     * Add a span_term query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function spanTerm($field, $value = null): static
    {
        return $this->addQuery(SpanTerm::create($field, $value));
    }

    /**
     * Add a span_within query.
     *
     * @param mixed $value
     * @return $this
     */
    public function spanWithin($value): static
    {
        return $this->addQuery(SpanWithin::create($value));
    }
}

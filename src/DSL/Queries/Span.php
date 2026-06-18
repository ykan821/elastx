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
     * @param mixed $spanContaining
     * @return $this
     */
    public function spanContaining($spanContaining): static
    {
        return $this->addQuery(SpanContaining::create($spanContaining));
    }

    /**
     * Add a span_field_masking query.
     *
     * @param mixed $spanFieldMasking
     * @return $this
     */
    public function spanFieldMasking($spanFieldMasking): static
    {
        return $this->addQuery(SpanFieldMasking::create($spanFieldMasking));
    }

    /**
     * Add a span_first query.
     *
     * @param mixed $spanFirst
     * @return $this
     */
    public function spanFirst($spanFirst): static
    {
        return $this->addQuery(SpanFirst::create($spanFirst));
    }

    /**
     * Add a span_multi query.
     *
     * @param mixed $spanMulti
     * @return $this
     */
    public function spanMulti($spanMulti): static
    {
        return $this->addQuery(SpanMulti::create($spanMulti));
    }

    /**
     * Add a span_near query.
     *
     * @param mixed $spanNear
     * @return $this
     */
    public function spanNear($spanNear): static
    {
        return $this->addQuery(SpanNear::create($spanNear));
    }

    /**
     * Add a span_not query.
     *
     * @param mixed $spanNot
     * @return $this
     */
    public function spanNot($spanNot): static
    {
        return $this->addQuery(SpanNot::create($spanNot));
    }

    /**
     * Add a span_or query.
     *
     * @param mixed $spanOr
     * @return $this
     */
    public function spanOr($spanOr): static
    {
        return $this->addQuery(SpanOr::create($spanOr));
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
     * @param mixed $spanWithin
     * @return $this
     */
    public function spanWithin($spanWithin): static
    {
        return $this->addQuery(SpanWithin::create($spanWithin));
    }
}

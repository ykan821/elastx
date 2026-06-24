<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Compound\Boosting;
use ElasticKit\DSL\Queries\Compound\ConstantScore;
use ElasticKit\DSL\Queries\Compound\DisjunctionMax;
use ElasticKit\DSL\Queries\Compound\FunctionScore;

/**
 * Shortcut methods for compound query types.
 */
trait Compound
{
    /**
     * Add a bool query.
     *
     * Supports:
     * bool(closure|Boolean)              — full control over the bool query
     * bool(['must' => value, ...])       — array of bool clauses
     * bool('must', $query)               — set a single clause (two-arg form)
     * bool('minimum_should_match', 1)    — set a single property (two-arg form)
     *
     * @example $query->bool(function (Boolean $b) { $b->must(function (Query $q) { $q->match('title', 'test') }) })
     *
     * @param mixed $field Boolean instance, closure, array, or a clause/property key (two-arg form)
     * @param mixed $value value for the two-arg form
     * @return $this
     */
    public function bool($field = null, $value = null): static
    {
        return $this->addQuery(Boolean::create($field, $value));
    }

    /**
     * Add a boosting query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function boosting($field = null, $value = null): static
    {
        return $this->addQuery(Boosting::create($field, $value));
    }

    /**
     * Add a constant_score query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function constantScore($field = null, $value = null): static
    {
        return $this->addQuery(ConstantScore::create($field, $value));
    }

    /**
     * Add a dis_max query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function disMax($field = null, $value = null): static
    {
        return $this->addQuery(DisjunctionMax::create($field, $value));
    }

    /**
     * Add a function_score query.
     *
     * @param mixed $value
     * @return $this
     */
    public function functionScore($value): static
    {
        return $this->addQuery(FunctionScore::create($value));
    }
}

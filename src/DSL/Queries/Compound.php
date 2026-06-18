<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Query;
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
     * Supports three forms:
     * bool(closure|Boolean)          — full control over the bool query
     * bool(['must' => value, ...])   — array of bool clauses
     *
     * @example $query->bool(function (Boolean $b) { $b->must(function (Query $q) { $q->match('title', 'test') }) })
     *
     * @param callable|Boolean|array<string, mixed> $value
     * @return $this
     */
    public function bool($value): static
    {
        if (is_array($value)) {
            $boolean = new Boolean();
            foreach ($value as $clause => $val) {
                $method = $clause === 'must_not' ? 'mustNot' : $clause;
                if ($val instanceof \Closure || $val instanceof Query) {
                    $boolean->$method($val);
                } else {
                    $boolean->addProperty($clause, $val);
                }
            }
            return $this->addQuery($boolean);
        }
        return $this->addQuery(Boolean::create($value));
    }

    /**
     * Add a boosting query.
     *
     * @param callable|Boosting|array<string, mixed> $value
     * @return $this
     */
    public function boosting($value): static
    {
        if (is_array($value)) {
            $b = new Boosting();
            foreach ($value as $key => $val) {
                if (($key === 'positive' || $key === 'negative')
                    && ($val instanceof \Closure || $val instanceof Query)) {
                    $b->$key($val);
                } else {
                    $b->addProperty($key, $val);
                }
            }
            return $this->addQuery($b);
        }
        return $this->addQuery(Boosting::create($value));
    }

    /**
     * Add a constant_score query.
     *
     * @param callable|ConstantScore|array<string, mixed> $value
     * @return $this
     */
    public function constantScore($value): static
    {
        if (is_array($value)) {
            $cs = new ConstantScore();
            foreach ($value as $key => $val) {
                if ($key === 'filter' && ($val instanceof \Closure || $val instanceof Query)) {
                    $cs->filter($val);
                } else {
                    $cs->addProperty($key, $val);
                }
            }
            return $this->addQuery($cs);
        }
        return $this->addQuery(ConstantScore::create($value));
    }

    /**
     * Add a dis_max query.
     *
     * @param callable|DisjunctionMax|array<string, mixed> $value
     * @return $this
     */
    public function disMax($value): static
    {
        if (is_array($value)) {
            $dm = new DisjunctionMax();
            foreach ($value as $key => $val) {
                if ($key === 'queries' && ($val instanceof \Closure || $val instanceof Query)) {
                    $dm->queries($val);
                } else {
                    $dm->addProperty($key, $val);
                }
            }
            return $this->addQuery($dm);
        }
        return $this->addQuery(DisjunctionMax::create($value));
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

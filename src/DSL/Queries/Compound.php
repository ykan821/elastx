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
     * @param callable|Boolean|array<string, mixed> $bool
     * @return $this
     */
    public function bool($bool): static
    {
        if (is_array($bool)) {
            $boolean = new Boolean();
            foreach ($bool as $clause => $val) {
                $method = $clause === 'must_not' ? 'mustNot' : $clause;
                if ($val instanceof \Closure || $val instanceof Query) {
                    $boolean->$method($val);
                } else {
                    $boolean->addProperty($clause, $val);
                }
            }
            return $this->addQuery($boolean);
        }
        return $this->addQuery(Boolean::create($bool));
    }

    /**
     * Add a boosting query.
     *
     * @param callable|Boosting|array<string, mixed> $boosting
     * @return $this
     */
    public function boosting($boosting): static
    {
        if (is_array($boosting)) {
            $b = new Boosting();
            foreach ($boosting as $key => $val) {
                if (($key === 'positive' || $key === 'negative')
                    && ($val instanceof \Closure || $val instanceof Query)) {
                    $b->$key($val);
                } else {
                    $b->addProperty($key, $val);
                }
            }
            return $this->addQuery($b);
        }
        return $this->addQuery(Boosting::create($boosting));
    }

    /**
     * Add a constant_score query.
     *
     * @param callable|ConstantScore|array<string, mixed> $constantScore
     * @return $this
     */
    public function constantScore($constantScore): static
    {
        if (is_array($constantScore)) {
            $cs = new ConstantScore();
            foreach ($constantScore as $key => $val) {
                if ($key === 'filter' && ($val instanceof \Closure || $val instanceof Query)) {
                    $cs->filter($val);
                } else {
                    $cs->addProperty($key, $val);
                }
            }
            return $this->addQuery($cs);
        }
        return $this->addQuery(ConstantScore::create($constantScore));
    }

    /**
     * Add a dis_max query.
     *
     * @param callable|DisjunctionMax|array<string, mixed> $disMax
     * @return $this
     */
    public function disMax($disMax): static
    {
        if (is_array($disMax)) {
            $dm = new DisjunctionMax();
            foreach ($disMax as $key => $val) {
                if ($key === 'queries' && ($val instanceof \Closure || $val instanceof Query)) {
                    $dm->queries($val);
                } else {
                    $dm->addProperty($key, $val);
                }
            }
            return $this->addQuery($dm);
        }
        return $this->addQuery(DisjunctionMax::create($disMax));
    }

    /**
     * Add a function_score query.
     *
     * @param mixed $functionScore
     * @return $this
     */
    public function functionScore($functionScore): static
    {
        return $this->addQuery(FunctionScore::create($functionScore));
    }
}

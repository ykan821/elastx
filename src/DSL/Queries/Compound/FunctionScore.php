<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\Compound\Functions\Function_;

/**
 * Modifies the score of documents retrieved by a query using one or more score functions.
 */
class FunctionScore extends Node
{
    protected string $_key = 'function_score';

    /**
     * Controls how the computed scores from multiple functions are combined.
     * Options: multiply (default), sum, avg, first, max, min.
     *
     * @param string $scoreMode
     * @return static
     */
    public function scoreMode(string $scoreMode): static
    {
        return $this->addProperty('score_mode', $scoreMode);
    }

    /**
     * Defines how the newly computed function score is combined with the query score.
     * Options: multiply (default), replace, sum, avg, max, min.
     *
     * @param string $boostMode
     * @return static
     */
    public function boostMode(string $boostMode): static
    {
        return $this->addProperty('boost_mode', $boostMode);
    }

    /**
     * Excludes documents that do not meet the specified score threshold.
     *
     * @param float $minScore
     * @return static
     */
    public function minScore(float $minScore): static
    {
        return $this->addProperty('min_score', $minScore);
    }

    /**
     * Restricts the new score to not exceed the specified limit. Defaults to FLT_MAX.
     *
     * @param float $maxBoost
     * @return static
     */
    public function maxBoost(float $maxBoost): static
    {
        return $this->addProperty('max_boost', $maxBoost);
    }

    /**
     * The query to be scored.
     *
     * @param mixed $query
     * @return static
     */
    public function query($query): static
    {
        return $this->addProperty('query', Query::create($query));
    }

    /**
     * Array of score functions to apply.
     *
     * @param array<int, mixed> $functions
     * @return static
     */
    public function functions(array $functions): static
    {
        return $this->addProperty('functions', $functions);
    }

    /**
     * Appends a score function to the functions array.
     *
     * @param mixed $function
     * @return static
     */
    public function addFunction($function): static
    {
        return $this->addProperty('functions', Function_::create($function), true);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $properties = $this->_properties;

        if (!empty($properties['functions'])) {
            foreach ($properties['functions'] as $key => $function) {
                if ($function instanceof Function_) {
                    $properties['functions'][$key] = $function->toArray();
                } elseif ($function instanceof Node) {
                    $properties['functions'][$key] = [$function->key() => $function->toArray()];
                } elseif (!empty($function['filter']) && $function['filter'] instanceof Node) {
                    $function['filter'] = $function['filter']->toArray()['query'];
                    $properties['functions'][$key] = $function;
                }
            }
        }

        $properties = $this->resolveProperties($properties);

        if ($this->_fieldKeyed) {
            return [$this->_field => $properties];
        }
        return $properties;
    }
}

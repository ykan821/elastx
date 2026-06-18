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
     * @param string $value
     * @return static
     */
    public function scoreMode(string $value): static
    {
        return $this->addProperty('score_mode', $value);
    }

    /**
     * Defines how the newly computed function score is combined with the query score.
     * Options: multiply (default), replace, sum, avg, max, min.
     *
     * @param string $value
     * @return static
     */
    public function boostMode(string $value): static
    {
        return $this->addProperty('boost_mode', $value);
    }

    /**
     * Excludes documents that do not meet the specified score threshold.
     *
     * @param float $value
     * @return static
     */
    public function minScore(float $value): static
    {
        return $this->addProperty('min_score', $value);
    }

    /**
     * Restricts the new score to not exceed the specified limit. Defaults to FLT_MAX.
     *
     * @param float $value
     * @return static
     */
    public function maxBoost(float $value): static
    {
        return $this->addProperty('max_boost', $value);
    }

    /**
     * The query to be scored.
     *
     * @param mixed $value
     * @return static
     */
    public function query($value): static
    {
        return $this->addProperty('query', Query::create($value));
    }

    /**
     * Array of score functions to apply.
     *
     * @param array<int, mixed> $value
     * @return static
     */
    public function functions(array $value): static
    {
        return $this->addProperty('functions', $value);
    }

    /**
     * Appends a score function to the functions array.
     *
     * @param mixed $value
     * @return static
     */
    public function addFunction($value): static
    {
        return $this->addProperty('functions', Function_::create($value), true);
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

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound\Functions;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\Script;

/**
 * Builds a single function entry within the function_score functions array, optionally with a filter and weight.
 */
class Function_ extends Node
{
    protected string $_key = 'function';

    /**
     * A filtering query that restricts the score function to matching documents only.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        return $this->addProperty('filter', Query::create($value));
    }

    /**
     * Multiplies the score by the provided weight value.
     *
     * @param float $value
     * @return static
     */
    public function weight(float $value): static
    {
        return $this->addProperty('weight', $value);
    }

    /**
     * Generates uniformly distributed random scores from 0 up to but not including 1.
     *
     * @param mixed $value
     * @return static
     */
    public function randomScore($value = null): static
    {
        return $this->addProperty('random_score', RandomScore::create($value));
    }

    /**
     * Wraps another query and customizes the scoring using a script.
     *
     * @param mixed $value
     * @return static
     */
    public function scriptScore($value): static
    {
        return $this->addProperty('script_score', ScriptScore::create($value));
    }

    /**
     * Sets the script for script_score using a Script object or closure.
     *
     * @param mixed $value
     * @return static
     */
    public function script($value): static
    {
        $valueScore = (new ScriptScore())->script($value);
        return $this->addProperty('script_score', $valueScore);
    }

    /**
     * Uses a numeric field value to influence the score.
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public function fieldValueFactor($field, $value = null): static
    {
        return $this->addProperty('field_value_factor', FieldValueFactor::create($field, $value));
    }

    /**
     * Scores documents using normal (Gaussian) decay based on distance from an origin point.
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public function gauss($field, $value = null): static
    {
        return $this->addProperty('gauss', Gauss::create($field, $value));
    }

    /**
     * Scores documents using linear decay based on distance from an origin point.
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public function linear($field, $value = null): static
    {
        return $this->addProperty('linear', Linear::create($field, $value));
    }

    /**
     * Scores documents using exponential decay based on distance from an origin point.
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public function exp($field, $value = null): static
    {
        return $this->addProperty('exp', Exp::create($field, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->_properties as $key => $value) {
            if ($value instanceof Query) {
                $result[$key] = $value->toArray()['query'];
            } elseif ($value instanceof Node) {
                $result[$value->key()] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

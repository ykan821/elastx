<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;
use RuntimeException;

/**
 * Returns documents based on the order and proximity of matching terms.
 * The intervals query uses matching rules, constructed from a small set of
 * definitions. These rules are then applied to terms from a specified field.
 */
class Intervals extends Node
{
    protected string $_key = 'intervals';

    protected bool $_fieldKeyed = true;

    /** @var array<int, mixed> */
    protected array $_intervals = [];

    /**
     * Add a match rule that matches analyzed text.
     *
     * @param mixed $value
     * @return static
     */
    public function match($value): static
    {
        $this->_intervals[] = Intervals\Match_::create($value);
        return $this;
    }

    /**
     * Add a prefix rule that matches terms that start with a specified set
     * of characters.
     *
     * @param mixed $value
     * @return static
     */
    public function prefix($value): static
    {
        $this->_intervals[] = Intervals\Prefix::create($value);
        return $this;
    }

    /**
     * Add a wildcard rule that matches terms using a wildcard pattern.
     *
     * @param mixed $value
     * @return static
     */
    public function wildcard($value): static
    {
        $this->_intervals[] = Intervals\Wildcard::create($value);
        return $this;
    }

    /**
     * Add a regexp rule that matches terms using a regular expression
     * pattern.
     *
     * @param mixed $value
     * @return static
     */
    public function regexp($value): static
    {
        $this->_intervals[] = Intervals\Regexp::create($value);
        return $this;
    }

    /**
     * Add a fuzzy rule that matches terms that are similar to the provided
     * term, within a defined edit distance.
     *
     * @param mixed $value
     * @return static
     */
    public function fuzzy($value): static
    {
        $this->_intervals[] = Intervals\Fuzzy::create($value);
        return $this;
    }

    /**
     * Add a range rule that matches terms that fall within a specified range.
     *
     * @param mixed $value
     * @return static
     */
    public function range($value): static
    {
        $this->_intervals[] = Intervals\Range::create($value);
        return $this;
    }

    /**
     * Add an all_of rule that returns matches that span a combination of
     * other rules.
     *
     * @param mixed $value
     * @return static
     */
    public function allOf($value): static
    {
        $this->_intervals[] = Intervals\AllOf::create($value);
        return $this;
    }

    /**
     * Add an any_of rule that returns intervals produced by any of its
     * sub-rules.
     *
     * @param mixed $value
     * @return static
     */
    public function anyOf($value): static
    {
        $this->_intervals[] = Intervals\AnyOf::create($value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $resolved = [];
        foreach ($this->_intervals as $interval) {
            if ($interval instanceof Node) {
                $resolved[] = [$interval->key() => $interval->toArray()];
            } else {
                $resolved[] = $interval;
            }
        }
        if (!$this->_multi) {
            $properties = $this->mergeUnique($resolved);
            if (!empty($this->_properties)) {
                $properties = $this->mergeUnique([$properties, $this->resolveProperties($this->_properties)]);
            }
        } else {
            $properties = $resolved;
        }

        return $this->wrapFieldKeyed($properties);
    }

    /**
     * Merge clause arrays, throwing on duplicate keys instead of silently overwriting.
     *
     * @param array<int, array<string, mixed>> $clauses
     * @return array<string, mixed>
     * @throws RuntimeException when two clauses share a key
     */
    private function mergeUnique(array $clauses): array
    {
        $merged = [];
        foreach ($clauses as $clause) {
            $clash = array_intersect_key($merged, $clause);
            if ($clash) {
                throw new RuntimeException(sprintf('Duplicate interval clause key "%s".', implode('", "', array_keys($clash))));
            }
            $merged += $clause;
        }
        return $merged;
    }
}

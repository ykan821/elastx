<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

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
     * @param mixed $match
     * @return static
     */
    public function match($match): static
    {
        $this->_intervals[] = Intervals\Match_::create($match);
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
            $properties = array_reduce($resolved, function ($carry, $item) {
                return array_merge($carry, $item);
            }, []);
        } else {
            $properties = $resolved;
        }

        if ($this->_fieldKeyed) {
            return [$this->_field => $properties];
        }
        return $properties;
    }
}

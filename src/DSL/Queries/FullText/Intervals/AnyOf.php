<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\FullText\Intervals;

/**
 * The any_of rule returns intervals produced by any of its sub-rules.
 */
class AnyOf extends Node
{
    protected string $_key = 'any_of';

    /**
     * An array of rules to match.
     *
     * @param mixed $value
     * @return static
     */
    public function intervals($value): static
    {
        $value = Intervals::create($value)
            ->fieldKeyed(false)
            ->multi(true);
        return $this->addProperty('intervals', $value);
    }

    /**
     * Append an interval rule. Supports multiple calls to incrementally build.
     *
     * @param mixed $value
     * @return static
     */
    public function addInterval($value): static
    {
        if (!isset($this->_properties['intervals'])) {
            $this->_properties['intervals'] = (new Intervals())->fieldKeyed(false)->multi(true);
        }
        $target = $this->_properties['intervals'];
        if ($value instanceof \Closure) {
            $value($target);
        }
        return $this;
    }

    /**
     * Rule used to filter returned
     * intervals.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        return $this->addProperty('filter', Filter::create($value));
    }
}

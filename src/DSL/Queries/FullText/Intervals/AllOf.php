<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\FullText\Intervals;

/**
 * The all_of rule returns matches that span a combination of other rules.
 */
class AllOf extends Node
{
    protected string $_key = 'all_of';

    /**
     * An array of rules to combine. All rules
     * must produce a match in a document for the overall source to match.
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
        } elseif ($value instanceof Node) {
            $target->addQuery($value);
        }
        return $this;
    }

    /**
     * Maximum number of positions between the matching
     * terms. Intervals produced by the rules further apart than this are not
     * considered matches. Defaults to -1 (no restriction).
     *
     * @param int $value
     * @return static
     */
    public function maxGaps(int $value): static
    {
        return $this->addProperty('max_gaps', $value);
    }

    /**
     * If true, intervals produced by the rules should
     * appear in the order in which they are specified. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ordered(bool $value): static
    {
        return $this->addProperty('ordered', $value);
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
        return $this->addProperty('filter', $value);
    }
}

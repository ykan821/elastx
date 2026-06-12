<?php

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Support\RangeSupport;

/**
 * The range rule matches terms that fall within a specified range of values.
 * Intervals are produced for each term in the range.
 */
class Range extends Node
{
    use RangeSupport;

    protected $_key = 'range';

    /**
     * (Optional) Greater than or equal to the specified value.
     *
     * @param mixed $gte
     * @return static
     */
    public function gte($gte)
    {
        return $this->addProperty('gte', $gte);
    }

    /**
     * (Optional) Greater than the specified value.
     *
     * @param mixed $gt
     * @return static
     */
    public function gt($gt)
    {
        return $this->addProperty('gt', $gt);
    }

    /**
     * (Optional) Less than or equal to the specified value.
     *
     * @param mixed $lte
     * @return static
     */
    public function lte($lte)
    {
        return $this->addProperty('lte', $lte);
    }

    /**
     * (Optional) Less than the specified value.
     *
     * @param mixed $lt
     * @return static
     */
    public function lt($lt)
    {
        return $this->addProperty('lt', $lt);
    }

    /**
     * Analyzer used to normalize the range values.
     * Defaults to the top-level field's analyzer.
     *
     * @param string $analyzer
     * @return static
     */
    public function analyzer($analyzer)
    {
        return $this->addProperty('analyzer', $analyzer);
    }

    /**
     * If specified, match intervals from this field rather
     * than the top-level field.
     *
     * @param string $useField
     * @return static
     */
    public function useField($useField)
    {
        return $this->addProperty('use_field', $useField);
    }
}

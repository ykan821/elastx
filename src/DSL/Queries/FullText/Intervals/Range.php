<?php

declare(strict_types=1);

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

    protected string $_key = 'range';

    /**
     * Greater than or equal to the specified value.
     *
     * @param string|int|float|bool $gte
     * @return static
     */
    public function gte(string|int|float|bool $gte): static
    {
        return $this->addProperty('gte', $gte);
    }

    /**
     * Greater than the specified value.
     *
     * @param string|int|float|bool $gt
     * @return static
     */
    public function gt(string|int|float|bool $gt): static
    {
        return $this->addProperty('gt', $gt);
    }

    /**
     * Less than or equal to the specified value.
     *
     * @param string|int|float|bool $lte
     * @return static
     */
    public function lte(string|int|float|bool $lte): static
    {
        return $this->addProperty('lte', $lte);
    }

    /**
     * Less than the specified value.
     *
     * @param string|int|float|bool $lt
     * @return static
     */
    public function lt(string|int|float|bool $lt): static
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
    public function analyzer(string $analyzer): static
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
    public function useField(string $useField): static
    {
        return $this->addProperty('use_field', $useField);
    }
}

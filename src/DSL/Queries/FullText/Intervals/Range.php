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
     * @param string|int|float|bool $value
     * @return static
     */
    public function gte(string|int|float|bool $value): static
    {
        return $this->addProperty('gte', $value);
    }

    /**
     * Greater than the specified value.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function gt(string|int|float|bool $value): static
    {
        return $this->addProperty('gt', $value);
    }

    /**
     * Less than or equal to the specified value.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function lte(string|int|float|bool $value): static
    {
        return $this->addProperty('lte', $value);
    }

    /**
     * Less than the specified value.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function lt(string|int|float|bool $value): static
    {
        return $this->addProperty('lt', $value);
    }

    /**
     * Analyzer used to normalize the range values.
     * Defaults to the top-level field's analyzer.
     *
     * @param string $value
     * @return static
     */
    public function analyzer(string $value): static
    {
        return $this->addProperty('analyzer', $value);
    }

    /**
     * If specified, match intervals from this field rather
     * than the top-level field.
     *
     * @param string $value
     * @return static
     */
    public function useField(string $value): static
    {
        return $this->addProperty('use_field', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;

/**
 * The wildcard rule matches terms using a wildcard pattern. This pattern can
 * expand to match at most 128 terms. If the pattern matches more than 128
 * terms, Elasticsearch returns an error.
 */
class Wildcard extends Node
{
    protected string $_key = 'wildcard';

    /**
     * Wildcard pattern used to find matching terms.
     * Supports ? (any single character) and * (zero or more characters).
     *
     * @param string $value
     * @return static
     */
    public function pattern(string $value): static
    {
        return $this->addProperty('pattern', $value);
    }

    /**
     * Analyzer used to normalize the pattern. Defaults to
     * the top-level field's analyzer.
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
     * than the top-level field. The pattern is normalized using the search
     * analyzer from this field.
     *
     * @param string $value
     * @return static
     */
    public function useField(string $value): static
    {
        return $this->addProperty('use_field', $value);
    }
}

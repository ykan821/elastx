<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;

/**
 * The regexp rule matches terms using a regular expression pattern. This
 * pattern can expand to match at most `indices.query.bool.max_clause_count`
 * search setting terms. If the pattern matches more terms, Elasticsearch
 * returns an error.
 */
class Regexp extends Node
{
    protected string $_key = 'regexp';

    /**
     * Regexp pattern used to find matching terms.
     * Avoid wildcard patterns such as `.*` or `.*?+`, which can slow search.
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

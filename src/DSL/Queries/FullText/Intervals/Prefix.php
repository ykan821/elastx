<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;

/**
 * The prefix rule matches terms that start with a specified set of characters.
 * This prefix can expand to match at most 128 terms. If the prefix matches
 * more than 128 terms, Elasticsearch returns an error.
 */
class Prefix extends Node
{
    protected string $_key = 'prefix';

    /**
     * Beginning characters of terms you wish to find in
     * the top-level field.
     *
     * @param string $value
     * @return static
     */
    public function prefix(string $value): static
    {
        return $this->addProperty('prefix', $value);
    }

    /**
     * Analyzer used to normalize the prefix. Defaults to
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
     * than the top-level field. The prefix is normalized using the search
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

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;

/**
 * Match rule for intervals query. Named Match_ to avoid conflict with PHP 8.0's match keyword.
 *
 * The match rule matches analyzed text.
 */
class Match_ extends Node
{
    protected string $_key = 'match';

    /**
     * Text you wish to find in the provided field.
     *
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Maximum number of positions between the matching
     * terms. Terms further apart than this are not considered matches.
     * Defaults to -1 (no restriction). If set to 0, the terms must appear
     * next to each other.
     *
     * @param int $value
     * @return static
     */
    public function maxGaps(int $value): static
    {
        return $this->addProperty('max_gaps', $value);
    }

    /**
     * If true, matching terms must appear in their
     * specified order. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ordered(bool $value = false): static
    {
        return $this->addProperty('ordered', $value);
    }

    /**
     * Analyzer used to analyze terms in the query.
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
     * An optional interval filter.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        return $this->addProperty('filter', Filter::create($value));
    }

    /**
     * If specified, match intervals from this field rather
     * than the top-level field. Terms are analyzed using the search analyzer
     * from this field.
     *
     * @param string $value
     * @return static
     */
    public function useField(string $value): static
    {
        return $this->addProperty('use_field', $value);
    }
}

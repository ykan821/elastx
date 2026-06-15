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
     * @param string $query
     * @return static
     */
    public function query(string $query): static
    {
        return $this->addProperty('query', $query);
    }

    /**
     * Maximum number of positions between the matching
     * terms. Terms further apart than this are not considered matches.
     * Defaults to -1 (no restriction). If set to 0, the terms must appear
     * next to each other.
     *
     * @param int $maxGaps
     * @return static
     */
    public function maxGaps(int $maxGaps): static
    {
        return $this->addProperty('max_gaps', $maxGaps);
    }

    /**
     * If true, matching terms must appear in their
     * specified order. Defaults to false.
     *
     * @param bool $ordered
     * @return static
     */
    public function ordered(bool $ordered = false): static
    {
        return $this->addProperty('ordered', $ordered);
    }

    /**
     * Analyzer used to analyze terms in the query.
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
     * An optional interval filter.
     *
     * @param mixed $filter
     * @return static
     */
    public function filter($filter): static
    {
        return $this->addProperty('filter', Filter::create($filter));
    }

    /**
     * If specified, match intervals from this field rather
     * than the top-level field. Terms are analyzed using the search analyzer
     * from this field.
     *
     * @param string $useField
     * @return static
     */
    public function useField(string $useField): static
    {
        return $this->addProperty('use_field', $useField);
    }
}

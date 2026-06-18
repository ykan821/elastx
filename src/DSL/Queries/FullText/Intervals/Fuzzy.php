<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Node;

/**
 * The fuzzy rule matches terms that are similar to the provided term, within
 * an edit distance defined by Fuzziness. If the fuzzy expansion matches more
 * than 128 terms, Elasticsearch returns an error.
 */
class Fuzzy extends Node
{
    protected string $_key = 'fuzzy';

    /**
     * The term to match.
     *
     * @param string $value
     * @return static
     */
    public function term(string $value): static
    {
        return $this->addProperty('term', $value);
    }

    /**
     * Number of beginning characters left unchanged when
     * creating expansions. Defaults to 0.
     *
     * @param int $value
     * @return static
     */
    public function prefixLength(int $value): static
    {
        return $this->addProperty('prefix_length', $value);
    }

    /**
     * Indicates whether edits include transpositions of
     * two adjacent characters (ab -> ba). Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function transpositions(bool $value): static
    {
        return $this->addProperty('transpositions', $value);
    }

    /**
     * Maximum edit distance allowed for matching.
     * Defaults to auto.
     *
     * @param int|string $value
     * @return static
     */
    public function fuzziness(int|string $value): static
    {
        return $this->addProperty('fuzziness', $value);
    }

    /**
     * Analyzer used to normalize the term. Defaults to the
     * top-level field's analyzer.
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
     * than the top-level field. The term is normalized using the search
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

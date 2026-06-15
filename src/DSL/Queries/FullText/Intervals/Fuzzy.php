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
     * @param string $term
     * @return static
     */
    public function term(string $term): static
    {
        return $this->addProperty('term', $term);
    }

    /**
     * Number of beginning characters left unchanged when
     * creating expansions. Defaults to 0.
     *
     * @param int $prefixLength
     * @return static
     */
    public function prefixLength(int $prefixLength): static
    {
        return $this->addProperty('prefix_length', $prefixLength);
    }

    /**
     * Indicates whether edits include transpositions of
     * two adjacent characters (ab -> ba). Defaults to true.
     *
     * @param bool $transpositions
     * @return static
     */
    public function transpositions(bool $transpositions): static
    {
        return $this->addProperty('transpositions', $transpositions);
    }

    /**
     * Maximum edit distance allowed for matching.
     * Defaults to auto.
     *
     * @param int|string $fuzziness
     * @return static
     */
    public function fuzziness(int|string $fuzziness): static
    {
        return $this->addProperty('fuzziness', $fuzziness);
    }

    /**
     * Analyzer used to normalize the term. Defaults to the
     * top-level field's analyzer.
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
     * than the top-level field. The term is normalized using the search
     * analyzer from this field.
     *
     * @param string $useField
     * @return static
     */
    public function useField(string $useField): static
    {
        return $this->addProperty('use_field', $useField);
    }
}

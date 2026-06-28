<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * Match query type. Named Match_ to avoid conflict with PHP 8.0's match keyword.
 *
 * Returns documents that match a provided text, number, date or boolean value.
 * The provided text is analyzed before matching. The match query is the standard
 * query for performing a full-text search, including options for fuzzy matching.
 */
class Match_ extends Node
{
    protected string $_key = 'match';

    protected bool $_fieldKeyed = true;

    protected string $_valueKey = 'query';

    /**
     * Text, number, boolean value or date you wish to find
     * in the provided field. The match query analyzes any provided text before
     * performing a search.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function query(string|int|float|bool $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Analyzer used to convert the text in the query value
     * into tokens. Defaults to the index-time analyzer mapped for the field.
     *
     * @param string $value
     * @return static
     */
    public function analyzer(string $value): static
    {
        return $this->addProperty('analyzer', $value);
    }

    /**
     * If true, match phrase queries are automatically
     * created for multi-term synonyms. Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function autoGenerateSynonymsPhraseQuery(bool $value): static
    {
        return $this->addProperty('auto_generate_synonyms_phrase_query', $value);
    }

    /**
     * Maximum edit distance allowed for matching.
     * See Fuzziness for valid values and more information.
     *
     * @param int|string $value
     * @return static
     */
    public function fuzziness(int|string $value): static
    {
        return $this->addProperty('fuzziness', $value);
    }

    /**
     * Maximum number of terms to which the query will
     * expand. Defaults to 50.
     *
     * @param int $value
     * @return static
     */
    public function maxExpansions(int $value): static
    {
        return $this->addProperty('max_expansions', $value);
    }

    /**
     * Number of beginning characters left unchanged for
     * fuzzy matching. Defaults to 0.
     *
     * @param int $value
     * @return static
     */
    public function prefixLength(int $value): static
    {
        return $this->addProperty('prefix_length', $value);
    }

    /**
     * If true, edits for fuzzy matching include
     * transpositions of two adjacent characters (ab -> ba). Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function fuzzyTranspositions(bool $value): static
    {
        return $this->addProperty('fuzzy_transpositions', $value);
    }

    /**
     * Method used to rewrite the query. If the fuzziness
     * parameter is not 0, the match query uses a fuzzy_rewrite method of
     * top_terms_blended_freqs_${max_expansions} by default.
     *
     * @param string $value
     * @return static
     */
    public function fuzzyRewrite(string $value): static
    {
        return $this->addProperty('fuzzy_rewrite', $value);
    }

    /**
     * If true, format-based errors, such as providing a
     * text query value for a numeric field, are ignored. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function lenient(bool $value): static
    {
        return $this->addProperty('lenient', $value);
    }

    /**
     * Boolean logic used to interpret text in the query
     * value. Valid values are: OR (Default), AND.
     *
     * @param string $value
     * @return static
     */
    public function operator(string $value): static
    {
        return $this->addProperty('operator', $value);
    }

    /**
     * Minimum number of clauses that must match for a
     * document to be returned.
     *
     * @param int|string $value
     * @return static
     */
    public function minimumShouldMatch(int|string $value): static
    {
        return $this->addProperty('minimum_should_match', $value);
    }

    /**
     * Indicates whether no documents are returned if the
     * analyzer removes all tokens, such as when using a stop filter.
     * Valid values are: none (Default), all.
     *
     * @param string $value
     * @return static
     */
    public function zeroTermsQuery(string $value): static
    {
        return $this->addProperty('zero_terms_query', $value);
    }
}

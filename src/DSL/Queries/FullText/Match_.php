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
     * @param string|int|float|bool $query
     * @return static
     */
    public function query(string|int|float|bool $query): static
    {
        return $this->addProperty('query', $query);
    }

    /**
     * Analyzer used to convert the text in the query value
     * into tokens. Defaults to the index-time analyzer mapped for the field.
     *
     * @param string $analyzer
     * @return static
     */
    public function analyzer(string $analyzer): static
    {
        return $this->addProperty('analyzer', $analyzer);
    }

    /**
     * If true, match phrase queries are automatically
     * created for multi-term synonyms. Defaults to true.
     *
     * @param bool $autoGenerateSynonymsPhraseQuery
     * @return static
     */
    public function autoGenerateSynonymsPhraseQuery(bool $autoGenerateSynonymsPhraseQuery): static
    {
        return $this->addProperty('auto_generate_synonyms_phrase_query', $autoGenerateSynonymsPhraseQuery);
    }

    /**
     * Maximum edit distance allowed for matching.
     * See Fuzziness for valid values and more information.
     *
     * @param int|string $fuzziness
     * @return static
     */
    public function fuzziness(int|string $fuzziness): static
    {
        return $this->addProperty('fuzziness', $fuzziness);
    }

    /**
     * Maximum number of terms to which the query will
     * expand. Defaults to 50.
     *
     * @param int $maxExpansions
     * @return static
     */
    public function maxExpansions(int $maxExpansions): static
    {
        return $this->addProperty('max_expansions', $maxExpansions);
    }

    /**
     * Number of beginning characters left unchanged for
     * fuzzy matching. Defaults to 0.
     *
     * @param int $prefixLength
     * @return static
     */
    public function prefixLength(int $prefixLength): static
    {
        return $this->addProperty('prefix_length', $prefixLength);
    }

    /**
     * If true, edits for fuzzy matching include
     * transpositions of two adjacent characters (ab -> ba). Defaults to true.
     *
     * @param bool $fuzzyTranspositions
     * @return static
     */
    public function fuzzyTranspositions(bool $fuzzyTranspositions): static
    {
        return $this->addProperty('fuzzy_transpositions', $fuzzyTranspositions);
    }

    /**
     * Method used to rewrite the query. If the fuzziness
     * parameter is not 0, the match query uses a fuzzy_rewrite method of
     * top_terms_blended_freqs_${max_expansions} by default.
     *
     * @param string $fuzzyRewrite
     * @return static
     */
    public function fuzzyRewrite(string $fuzzyRewrite): static
    {
        return $this->addProperty('fuzzy_rewrite', $fuzzyRewrite);
    }

    /**
     * If true, format-based errors, such as providing a
     * text query value for a numeric field, are ignored. Defaults to false.
     *
     * @param bool $lenient
     * @return static
     */
    public function lenient(bool $lenient): static
    {
        return $this->addProperty('lenient', $lenient);
    }

    /**
     * Boolean logic used to interpret text in the query
     * value. Valid values are: OR (Default), AND.
     *
     * @param string $operator
     * @return static
     */
    public function operator(string $operator): static
    {
        return $this->addProperty('operator', $operator);
    }

    /**
     * Minimum number of clauses that must match for a
     * document to be returned.
     *
     * @param int|string $minimumShouldMatch
     * @return static
     */
    public function minimumShouldMatch(int|string $minimumShouldMatch): static
    {
        return $this->addProperty('minimum_should_match', $minimumShouldMatch);
    }

    /**
     * Indicates whether no documents are returned if the
     * analyzer removes all tokens, such as when using a stop filter.
     * Valid values are: none (Default), all.
     *
     * @param string $zeroTermsQuery
     * @return static
     */
    public function zeroTermsQuery(string $zeroTermsQuery): static
    {
        return $this->addProperty('zero_terms_query', $zeroTermsQuery);
    }
}

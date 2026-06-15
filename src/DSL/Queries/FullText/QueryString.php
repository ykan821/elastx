<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * Returns documents based on a provided query string, using a parser with a
 * strict syntax. This query uses a syntax to parse and split the provided
 * query string based on operators, such as AND or NOT. The query then analyzes
 * each split text independently before returning matching documents.
 */
class QueryString extends Node
{
    protected string $_key = 'query_string';

    /**
     * Query string you wish to parse and use for search.
     *
     * @param string $query
     * @return static
     */
    public function query(string $query): static
    {
        return $this->addProperty('query', $query);
    }

    /**
     * Default field to search if no field is provided in
     * the query string. Supports wildcards (*). Defaults to *.
     *
     * @param string $defaultField
     * @return static
     */
    public function defaultField(string $defaultField): static
    {
        return $this->addProperty('default_field', $defaultField);
    }

    /**
     * If true, the wildcard characters * and ? are allowed
     * as the first character of the query string. Defaults to true.
     *
     * @param bool $allowLeadingWildcard
     * @return static
     */
    public function allowLeadingWildcard(bool $allowLeadingWildcard): static
    {
        return $this->addProperty('allow_leading_wildcard', $allowLeadingWildcard);
    }

    /**
     * Analyzer used to convert text in the query string
     * into tokens. Defaults to the index-time analyzer mapped for the
     * default_field.
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
     * Default boolean logic used to interpret text in the
     * query string if no operators are specified. Valid values are:
     * OR (Default), AND.
     *
     * @param string $defaultOperator
     * @return static
     */
    public function defaultOperator(string $defaultOperator): static
    {
        return $this->addProperty('default_operator', $defaultOperator);
    }

    /**
     * If true, enable position increments in queries
     * constructed from a query_string search. Defaults to true.
     *
     * @param bool $enablePositionIncrements
     * @return static
     */
    public function enablePositionIncrements(bool $enablePositionIncrements): static
    {
        return $this->addProperty('enable_position_increments', $enablePositionIncrements);
    }

    /**
     * Array of fields to search. Supports
     * wildcards (*).
     *
     * @param array<int, string> $fields
     * @return static
     */
    public function fields(array $fields): static
    {
        return $this->addProperty('fields', $fields);
    }

    /**
     * Maximum edit distance allowed for fuzzy matching.
     *
     * @param int|string $fuzziness
     * @return static
     */
    public function fuzziness(int|string $fuzziness): static
    {
        return $this->addProperty('fuzziness', $fuzziness);
    }

    /**
     * Maximum number of terms to which the query expands
     * for fuzzy matching. Defaults to 50.
     *
     * @param int $fuzzyMaxExpansions
     * @return static
     */
    public function fuzzyMaxExpansions(int $fuzzyMaxExpansions): static
    {
        return $this->addProperty('fuzzy_max_expansions', $fuzzyMaxExpansions);
    }

    /**
     * Number of beginning characters left unchanged for
     * fuzzy matching. Defaults to 0.
     *
     * @param int $fuzzyPrefixLength
     * @return static
     */
    public function fuzzyPrefixLength(int $fuzzyPrefixLength): static
    {
        return $this->addProperty('fuzzy_prefix_length', $fuzzyPrefixLength);
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
     * If true, format-based errors, such as providing a
     * text value for a numeric field, are ignored. Defaults to false.
     *
     * @param bool $lenient
     * @return static
     */
    public function lenient(bool $lenient): static
    {
        return $this->addProperty('lenient', $lenient);
    }

    /**
     * Maximum number of automaton states required for
     * the query. Default is 10000.
     *
     * @param int $maxDeterminizedStates
     * @return static
     */
    public function maxDeterminizedStates(int $maxDeterminizedStates): static
    {
        return $this->addProperty('max_determinized_states', $maxDeterminizedStates);
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
     * Analyzer used to convert quoted text in the query
     * string into tokens. Defaults to the search_quote_analyzer mapped for
     * the default_field.
     *
     * @param string $quoteAnalyzer
     * @return static
     */
    public function quoteAnalyzer(string $quoteAnalyzer): static
    {
        return $this->addProperty('quote_analyzer', $quoteAnalyzer);
    }

    /**
     * Maximum number of positions allowed between matching
     * tokens for phrases. Defaults to 0. If 0, exact phrase matches are
     * required. Transposed terms have a slop of 2.
     *
     * @param int $phraseSlop
     * @return static
     */
    public function phraseSlop(int $phraseSlop): static
    {
        return $this->addProperty('phrase_slop', $phraseSlop);
    }

    /**
     * Suffix appended to quoted text in the query string.
     * You can use this suffix to use a different analysis method for exact
     * matches.
     *
     * @param string $quoteFieldSuffix
     * @return static
     */
    public function quoteFieldSuffix(string $quoteFieldSuffix): static
    {
        return $this->addProperty('quote_field_suffix', $quoteFieldSuffix);
    }

    /**
     * Method used to rewrite the query.
     *
     * @param string $rewrite
     * @return static
     */
    public function rewrite(string $rewrite): static
    {
        return $this->addProperty('rewrite', $rewrite);
    }

    /**
     * Coordinated Universal Time (UTC) offset or IANA time
     * zone used to convert date values in the query string to UTC.
     *
     * @param string $timeZone
     * @return static
     */
    public function timeZone(string $timeZone): static
    {
        return $this->addProperty('time_zone', $timeZone);
    }

    /**
     * Determines how the query matches and scores documents
     * when searching multiple fields. Valid values are: best_fields (Default),
     * most_fields, cross_fields, phrase, phrase_prefix, bool_prefix.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->addProperty('type', $type);
    }

    /**
     * If true, the query attempts to analyze wildcard terms
     * in the query string. Defaults to false.
     *
     * @param bool $analyzeWildcard
     * @return static
     */
    public function analyzeWildcard(bool $analyzeWildcard): static
    {
        return $this->addProperty('analyze_wildcard', $analyzeWildcard);
    }

    /**
     * Floating point number used to control the scoring of
     * results when searching multiple fields. Defaults to 0.
     *
     * @param float $tieBreaker
     * @return static
     */
    public function tieBreaker(float $tieBreaker): static
    {
        return $this->addProperty('tie_breaker', $tieBreaker);
    }
}

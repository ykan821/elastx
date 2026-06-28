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
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Default field to search if no field is provided in
     * the query string. Supports wildcards (*). Defaults to *.
     *
     * @param string $value
     * @return static
     */
    public function defaultField(string $value): static
    {
        return $this->addProperty('default_field', $value);
    }

    /**
     * If true, the wildcard characters * and ? are allowed
     * as the first character of the query string. Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function allowLeadingWildcard(bool $value): static
    {
        return $this->addProperty('allow_leading_wildcard', $value);
    }

    /**
     * Analyzer used to convert text in the query string
     * into tokens. Defaults to the index-time analyzer mapped for the
     * default_field.
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
     * Default boolean logic used to interpret text in the
     * query string if no operators are specified. Valid values are:
     * OR (Default), AND.
     *
     * @param string $value
     * @return static
     */
    public function defaultOperator(string $value): static
    {
        return $this->addProperty('default_operator', $value);
    }

    /**
     * If true, enable position increments in queries
     * constructed from a query_string search. Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function enablePositionIncrements(bool $value): static
    {
        return $this->addProperty('enable_position_increments', $value);
    }

    /**
     * Array of fields to search. Supports
     * wildcards (*).
     *
     * @param array<int, string> $value
     * @return static
     */
    public function fields(array $value): static
    {
        return $this->addProperty('fields', $value);
    }

    /**
     * Maximum edit distance allowed for fuzzy matching.
     *
     * @param int|string $value
     * @return static
     */
    public function fuzziness(int|string $value): static
    {
        return $this->addProperty('fuzziness', $value);
    }

    /**
     * Maximum number of terms to which the query expands
     * for fuzzy matching. Defaults to 50.
     *
     * @param int $value
     * @return static
     */
    public function fuzzyMaxExpansions(int $value): static
    {
        return $this->addProperty('fuzzy_max_expansions', $value);
    }

    /**
     * Number of beginning characters left unchanged for
     * fuzzy matching. Defaults to 0.
     *
     * @param int $value
     * @return static
     */
    public function fuzzyPrefixLength(int $value): static
    {
        return $this->addProperty('fuzzy_prefix_length', $value);
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
     * If true, format-based errors, such as providing a
     * text value for a numeric field, are ignored. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function lenient(bool $value): static
    {
        return $this->addProperty('lenient', $value);
    }

    /**
     * Maximum number of automaton states required for
     * the query. Default is 10000.
     *
     * @param int $value
     * @return static
     */
    public function maxDeterminizedStates(int $value): static
    {
        return $this->addProperty('max_determinized_states', $value);
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
     * Analyzer used to convert quoted text in the query
     * string into tokens. Defaults to the search_quote_analyzer mapped for
     * the default_field.
     *
     * @param string $value
     * @return static
     */
    public function quoteAnalyzer(string $value): static
    {
        return $this->addProperty('quote_analyzer', $value);
    }

    /**
     * Maximum number of positions allowed between matching
     * tokens for phrases. Defaults to 0. If 0, exact phrase matches are
     * required. Transposed terms have a slop of 2.
     *
     * @param int $value
     * @return static
     */
    public function phraseSlop(int $value): static
    {
        return $this->addProperty('phrase_slop', $value);
    }

    /**
     * Suffix appended to quoted text in the query string.
     * You can use this suffix to use a different analysis method for exact
     * matches.
     *
     * @param string $value
     * @return static
     */
    public function quoteFieldSuffix(string $value): static
    {
        return $this->addProperty('quote_field_suffix', $value);
    }

    /**
     * Method used to rewrite the query.
     *
     * @param string $value
     * @return static
     */
    public function rewrite(string $value): static
    {
        return $this->addProperty('rewrite', $value);
    }

    /**
     * Coordinated Universal Time (UTC) offset or IANA time
     * zone used to convert date values in the query string to UTC.
     *
     * @param string $value
     * @return static
     */
    public function timeZone(string $value): static
    {
        return $this->addProperty('time_zone', $value);
    }

    /**
     * Determines how the query matches and scores documents
     * when searching multiple fields. Valid values are: best_fields (Default),
     * most_fields, cross_fields, phrase, phrase_prefix, bool_prefix.
     *
     * @param string $value
     * @return static
     */
    public function type(string $value): static
    {
        return $this->addProperty('type', $value);
    }

    /**
     * If true, the query attempts to analyze wildcard terms
     * in the query string. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function analyzeWildcard(bool $value): static
    {
        return $this->addProperty('analyze_wildcard', $value);
    }

    /**
     * Floating point number used to control the scoring of
     * results when searching multiple fields. Defaults to 0.
     *
     * @param float $value
     * @return static
     */
    public function tieBreaker(float $value): static
    {
        return $this->addProperty('tie_breaker', $value);
    }
}

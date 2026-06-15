<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * Returns documents based on a provided query string, using a parser with a
 * limited but fault-tolerant syntax. Unlike the query_string query, the
 * simple_query_string query does not return errors for invalid syntax. Instead,
 * it ignores any invalid parts of the query string.
 */
class SimpleQueryString extends Node
{
    protected string $_key = 'simple_query_string';

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
     * Array of fields you wish to search.
     * Supports wildcard expressions and per-field boosting with caret (^)
     * notation.
     *
     * @param array<int, string> $fields
     * @return static
     */
    public function fields(array $fields): static
    {
        return $this->addProperty('fields', $fields);
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
     * If true, the parser creates a match_phrase query
     * for each multi-position token. Defaults to true.
     *
     * @param bool $autoGenerateSynonymsPhraseQuery
     * @return static
     */
    public function autoGenerateSynonymsPhraseQuery(bool $autoGenerateSynonymsPhraseQuery): static
    {
        return $this->addProperty('auto_generate_synonyms_phrase_query', $autoGenerateSynonymsPhraseQuery);
    }

    /**
     * List of enabled operators for the simple query string
     * syntax. Defaults to ALL (all operators).
     *
     * @param string $flags
     * @return static
     */
    public function flags(string $flags): static
    {
        return $this->addProperty('flags', $flags);
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
}

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
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Array of fields you wish to search.
     * Supports wildcard expressions and per-field boosting with caret (^)
     * notation.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function fields(array $value): static
    {
        return $this->addProperty('fields', $value);
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
     * If true, the parser creates a match_phrase query
     * for each multi-position token. Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function autoGenerateSynonymsPhraseQuery(bool $value): static
    {
        return $this->addProperty('auto_generate_synonyms_phrase_query', $value);
    }

    /**
     * List of enabled operators for the simple query string
     * syntax. Defaults to ALL (all operators).
     *
     * @param string $value
     * @return static
     */
    public function flags(string $value): static
    {
        return $this->addProperty('flags', $value);
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
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * The multi_match query builds on the match query to allow multi-field queries.
 * Fields can be specified with wildcards and individual fields can be boosted
 * with the caret (^) notation.
 */
class MultiMatch extends Node
{
    protected string $_key = 'multi_match';

    /**
     * Text, number, boolean value or date you wish to find
     * in the provided fields.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function query(string|int|float|bool $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Array of fields to search. Supports
     * wildcards (*). Individual fields can be boosted with the caret (^)
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
     * Determines how the query matches and scores documents.
     * Valid values are: best_fields (Default), most_fields, cross_fields,
     * phrase, phrase_prefix, bool_prefix.
     *
     * @param string $value
     * @return static
     */
    public function type(string $value): static
    {
        return $this->addProperty('type', $value);
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
     * Floating point number used to decrease or increase
     * the relevance scores of a query. Defaults to 1.0.
     *
     * @param float $value
     * @return static
     */
    public function tieBreaker(float $value): static
    {
        return $this->addProperty('tie_breaker', $value);
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
     * Method used to rewrite the query for fuzzy matching.
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

    /**
     * Maximum number of positions allowed between matching
     * tokens. Defaults to 0. Transposed terms have a slop of 2.
     *
     * @param int $value
     * @return static
     */
    public function slop(int $value): static
    {
        return $this->addProperty('slop', $value);
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
}

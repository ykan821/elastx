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
     * @param string|int|float|bool $query
     * @return static
     */
    public function query(string|int|float|bool $query): static
    {
        return $this->addProperty('query', $query);
    }

    /**
     * Array of fields to search. Supports
     * wildcards (*). Individual fields can be boosted with the caret (^)
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
     * Determines how the query matches and scores documents.
     * Valid values are: best_fields (Default), most_fields, cross_fields,
     * phrase, phrase_prefix, bool_prefix.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->addProperty('type', $type);
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
     * Floating point number used to decrease or increase
     * the relevance scores of a query. Defaults to 1.0.
     *
     * @param float $tieBreaker
     * @return static
     */
    public function tieBreaker(float $tieBreaker): static
    {
        return $this->addProperty('tie_breaker', $tieBreaker);
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
     * Method used to rewrite the query for fuzzy matching.
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

    /**
     * Maximum number of positions allowed between matching
     * tokens. Defaults to 0. Transposed terms have a slop of 2.
     *
     * @param int $slop
     * @return static
     */
    public function slop(int $slop): static
    {
        return $this->addProperty('slop', $slop);
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
}

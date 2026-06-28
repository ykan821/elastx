<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * The match_bool_prefix query analyzes its input and constructs a bool query
 * from the terms. Each term except the last is used in a term query. The last
 * term is used in a prefix query.
 */
class MatchBoolPrefix extends Node
{
    protected string $_key = 'match_bool_prefix';

    protected bool $_fieldKeyed = true;

    protected string $_valueKey = 'query';

    /**
     * Text you wish to find in the provided field.
     * The match_bool_prefix query analyzes any provided text into tokens
     * before performing a search.
     *
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * Maximum number of terms to which the last provided
     * term of the query value will expand. Defaults to 50.
     *
     * @param int $value
     * @return static
     */
    public function maxExpansions(int $value): static
    {
        return $this->addProperty('max_expansions', $value);
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
     * Analyzer used to convert text in the query value
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
}

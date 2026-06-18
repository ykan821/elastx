<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * The combined_fields query supports searching multiple text fields as if their
 * contents had been indexed into one combined field. The query takes a
 * term-centric view of the input string: first it analyzes the query string
 * into individual terms, then looks for each term in any of the fields.
 */
class CombinedFields extends Node
{
    protected string $_key = 'combined_fields';

    /**
     * Text to search for in the provided fields.
     * The combined_fields query analyzes the provided text before performing
     * a search.
     *
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
    }

    /**
     * List of fields to search. Field wildcard
     * patterns are allowed. Only text fields are supported, and they must all
     * have the same search analyzer.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function fields(array $value): static
    {
        return $this->addProperty('fields', $value);
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
     * Boolean logic used to interpret text in the query
     * value. Valid values are: or (Default), and.
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

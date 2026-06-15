<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * Returns documents that contain the words of a provided text, in the same
 * order as provided. The last term of the provided text is treated as a prefix,
 * matching any words that begin with that term.
 */
class MatchPhrasePrefix extends Node
{
    protected string $_key = 'match_phrase_prefix';

    protected bool $_fieldKeyed = true;

    protected string $_valueKey = 'query';

    /**
     * Text you wish to find in the provided field.
     * The match_phrase_prefix query analyzes any provided text into tokens
     * before performing a search. The last term of this text is treated as a
     * prefix, matching any words that begin with that term.
     *
     * @param string $query
     * @return static
     */
    public function query(string $query): static
    {
        return $this->addProperty('query', $query);
    }

    /**
     * Analyzer used to convert text in the query value
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
     * Maximum number of terms to which the last provided
     * term of the query value will expand. Defaults to 50.
     *
     * @param int $maxExpansions
     * @return static
     */
    public function maxExpansions(int $maxExpansions): static
    {
        return $this->addProperty('max_expansions', $maxExpansions);
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

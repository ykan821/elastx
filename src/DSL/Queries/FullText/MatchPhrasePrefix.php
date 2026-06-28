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
     * @param string $value
     * @return static
     */
    public function query(string $value): static
    {
        return $this->addProperty('query', $value);
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

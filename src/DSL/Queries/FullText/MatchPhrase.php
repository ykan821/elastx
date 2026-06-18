<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText;

use ElasticKit\DSL\Node;

/**
 * The match_phrase query analyzes the text and creates a phrase query out of
 * the analyzed text. A phrase query matches terms up to a configurable slop
 * (which defaults to 0) in any order. Transposed terms have a slop of 2.
 */
class MatchPhrase extends Node
{
    protected string $_key = 'match_phrase';

    protected bool $_fieldKeyed = true;

    protected string $_valueKey = 'query';

    /**
     * Text you wish to find in the provided field.
     * The match_phrase query analyzes any provided text into tokens before
     * performing a search.
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

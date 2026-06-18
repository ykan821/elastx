<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Node;

/**
 * Finds documents similar to given text, documents, or collections of documents.
 */
class MoreLikeThis extends Node
{
    protected string $_key = 'more_like_this';

    /**
     * List of fields to use for similarity comparison.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function fields(array $value): static
    {
        return $this->addProperty('fields', $value);
    }

    /**
     * Text or documents to find similar documents for.
     *
     * @param mixed $value
     * @return static
     */
    public function like($value): static
    {
        return $this->addProperty('like', $value);
    }

    /**
     * Minimum term frequency below which terms are ignored. Defaults to 2.
     *
     * @param int $value
     * @return static
     */
    public function minTermFreq(int $value): static
    {
        return $this->addProperty('min_term_freq', $value);
    }

    /**
     * Maximum number of query terms to be selected per result document. Defaults to 25.
     *
     * @param int $value
     * @return static
     */
    public function maxQueryTerms(int $value): static
    {
        return $this->addProperty('max_query_terms', $value);
    }
}

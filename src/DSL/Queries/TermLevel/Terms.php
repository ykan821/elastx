<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

class Terms extends Node
{
    protected string $_key = 'terms';

    protected bool $_fieldKeyed = true;

    /**
     * Field you wish to search.
     *
     * The value of this parameter is an array of terms you wish to find in the provided field. To return a document, one or more terms must exactly match a field value, including whitespace and capitalization.
     *
     * By default, Elasticsearch limits the terms query to a maximum of 65,536 terms. You can change this limit using the index.max_terms_count setting.
     *
     * @param string $field
     * @param array<int, string|int|float|bool> $values
     * @return static
     */
    public function values(string $field, array $values): static
    {
        return $this->addProperty($field, $values);
    }
}

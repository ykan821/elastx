<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Joining;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Returns child documents whose joined parent document matches a provided query.
 *
 * You can create parent-child relationships between documents in the same index using a join field mapping.
 */
class HasParent extends Node
{
    protected string $_key = 'has_parent';

    /**
     * Name of the parent relationship mapped for the join field.
     *
     * @param string $value
     * @return static
     */
    public function parentType(string $value): static
    {
        return $this->addProperty('parent_type', $value);
    }

    /**
     * Query you wish to run on parent documents of the parent_type field.
     * If a parent document matches the search, the query returns its child documents.
     *
     * @param mixed $value
     * @return static
     */
    public function query($value): static
    {
        return $this->addProperty('query', Query::create($value));
    }

    /**
     * Indicates whether the relevance score of a matching parent document is
     * aggregated into its child documents. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function score(bool $value): static
    {
        return $this->addProperty('score', $value);
    }

    /**
     * Indicates whether to ignore an unmapped parent_type and not return any
     * documents instead of an error. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ignoreUnmapped(bool $value): static
    {
        return $this->addProperty('ignore_unmapped', $value);
    }
}

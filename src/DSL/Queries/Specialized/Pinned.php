<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Promotes selected documents to the top of the search results.
 */
class Pinned extends Node
{
    protected string $_key = 'pinned';

    /**
     * List of document IDs to pin to the top of the results.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function ids(array $value): static
    {
        return $this->addProperty('ids', $value);
    }

    /**
     * The organic query used to rank non-pinned documents.
     *
     * @param mixed $value
     * @return static
     */
    public function organic($value): static
    {
        return $this->addProperty('organic', Query::create($value));
    }

    /**
     * A document to pin instead of using an ID.
     *
     * @param mixed $value
     * @return static
     */
    public function doc($value): static
    {
        return $this->addProperty('doc', $value);
    }
}

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
     * @param array<int, string> $ids
     * @return static
     */
    public function ids(array $ids): static
    {
        return $this->addProperty('ids', $ids);
    }

    /**
     * The organic query used to rank non-pinned documents.
     *
     * @param mixed $organic
     * @return static
     */
    public function organic($organic): static
    {
        return $this->addProperty('organic', Query::create($organic));
    }

    /**
     * A document to pin instead of using an ID.
     *
     * @param mixed $doc
     * @return static
     */
    public function doc($doc): static
    {
        return $this->addProperty('doc', $doc);
    }
}

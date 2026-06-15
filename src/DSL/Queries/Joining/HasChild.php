<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Joining;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Returns parent documents whose joined child documents match a provided query.
 *
 * You can create parent-child relationships between documents in the same index using a join field mapping.
 */
class HasChild extends Node
{
    protected string $_key = 'has_child';

    /**
     * Name of the child relationship mapped for the join field.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->addProperty('type', $type);
    }

    /**
     * Query you wish to run on child documents of the type field.
     * If a child document matches the search, the query returns the parent document.
     *
     * @param mixed $query
     * @return static
     */
    public function query($query): static
    {
        return $this->addProperty('query', Query::create($query));
    }

    /**
     * Indicates whether to ignore an unmapped type and not return any
     * documents instead of an error. Defaults to false.
     *
     * @param bool $ignoreUnmapped
     * @return static
     */
    public function ignoreUnmapped(bool $ignoreUnmapped): static
    {
        return $this->addProperty('ignore_unmapped', $ignoreUnmapped);
    }

    /**
     * Maximum number of child documents that match the query allowed for a
     * returned parent document. If the parent document exceeds this limit, it is excluded from the search results.
     *
     * @param int $maxChildren
     * @return static
     */
    public function maxChildren(int $maxChildren): static
    {
        return $this->addProperty('max_children', $maxChildren);
    }

    /**
     * Minimum number of child documents that match the query required to match
     * the query for a returned parent document. If the parent document does not meet this limit,
     * it is excluded from the search results.
     *
     * @param int $minChildren
     * @return static
     */
    public function minChildren(int $minChildren): static
    {
        return $this->addProperty('min_children', $minChildren);
    }

    /**
     * Indicates how scores for matching child documents affect the root parent
     * document's relevance score. Valid values: none (default), avg, max, min, sum.
     *
     * @param string $scoreMode
     * @return static
     */
    public function scoreMode(string $scoreMode): static
    {
        return $this->addProperty('score_mode', $scoreMode);
    }
}

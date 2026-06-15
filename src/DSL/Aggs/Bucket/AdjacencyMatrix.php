<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that creates buckets based on the combinations of filter matches.
 */
class AdjacencyMatrix extends Node
{
    protected string $_key = 'adjacency_matrix';

    /**
     * Add a named filter used to create buckets.
     *
     * @param string $key
     * @param mixed $query
     * @return static
     */
    public function filters($key, $query): static
    {
        $this->_properties ??= [];
        $this->_properties['filters'][$key] = Query::create($query);
        return $this;
    }

    /**
     * Separator used to concatenate filter names. Defaults to &.
     *
     * @param string $separator
     * @return static
     */
    public function separator(string $separator): static
    {
        return $this->addProperty('separator', $separator);
    }
}

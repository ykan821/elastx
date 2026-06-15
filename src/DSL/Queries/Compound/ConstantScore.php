<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Wraps a filter query and returns every matching document with a relevance score equal to the boost parameter value.
 */
class ConstantScore extends Node
{
    protected string $_key = 'constant_score';

    /**
     * Filter query you wish to run. Any returned documents must match this query.
     *
     * @param mixed $query
     * @return static
     */
    public function filter($query): static
    {
        return $this->addProperty('filter', Query::create($query));
    }
}

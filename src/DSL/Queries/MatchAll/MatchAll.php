<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\MatchAll;

use ElasticKit\DSL\Node;

/**
 * Matches all documents. Useful when you want to apply filter clauses without
 * specifying a specific query.
 */
class MatchAll extends Node
{
    protected string $_key = 'match_all';

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->_properties ?: (object) [];
    }
}

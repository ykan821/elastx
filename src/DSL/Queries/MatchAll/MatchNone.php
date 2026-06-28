<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\MatchAll;

use ElasticKit\DSL\Node;

/**
 * Matches no documents. Useful when you need a query that never returns results.
 */
class MatchNone extends Node
{
    protected string $_key = 'match_none';

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->_properties ?: (object) [];
    }
}

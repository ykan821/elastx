<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

/**
 * Returns documents based on their IDs.
 */
class IDs extends Node
{
    protected string $_key = 'ids';

    /**
     * An array of document IDs.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function values(array $value): static
    {
        return $this->addProperty('values', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Node;

/**
 * Matches documents against registered percolator queries.
 */
class Percolate extends Node
{
    protected string $_key = 'percolate';

    /**
     * The source document to percolate against registered queries.
     *
     * @param mixed $document
     * @return static
     */
    public function document($document): static
    {
        return $this->addProperty('document', $document);
    }
}

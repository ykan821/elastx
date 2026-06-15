<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Joining;

use ElasticKit\DSL\Node;

/**
 * Returns child documents joined to a specific parent document.
 *
 * You can use a join field mapping to create parent-child relationships between documents in the same index.
 */
class ParentId extends Node
{
    protected string $_key = 'parent_id';

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
     * ID of the parent document. The query will return child documents of this parent document.
     *
     * @param string $id
     * @return static
     */
    public function id(string $id): static
    {
        return $this->addProperty('id', $id);
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
}

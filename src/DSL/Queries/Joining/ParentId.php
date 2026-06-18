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
     * @param string $value
     * @return static
     */
    public function type(string $value): static
    {
        return $this->addProperty('type', $value);
    }

    /**
     * ID of the parent document. The query will return child documents of this parent document.
     *
     * @param string $value
     * @return static
     */
    public function id(string $value): static
    {
        return $this->addProperty('id', $value);
    }

    /**
     * Indicates whether to ignore an unmapped type and not return any
     * documents instead of an error. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ignoreUnmapped(bool $value): static
    {
        return $this->addProperty('ignore_unmapped', $value);
    }
}

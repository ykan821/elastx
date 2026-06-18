<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Joining\HasChild;
use ElasticKit\DSL\Queries\Joining\HasParent;
use ElasticKit\DSL\Queries\Joining\Nested;
use ElasticKit\DSL\Queries\Joining\ParentId;

/**
 * Shortcut methods for joining query types.
 */
trait Joining
{
    /**
     * Add a nested query.
     *
     * @example $query->nested('comments', function (Query $q) { $q->match('title','test') })
     *
     * @param callable|string|Nested|array<string, mixed> $path path string, or full nested definition
     * @param callable|Query|array<string, mixed>|null $query optional query when first arg is a path string
     * @return $this
     */
    public function nested($path, $query = null): static
    {
        if (is_string($path) && $query !== null) {
            $nested = Nested::create($path);
            $nested->query($query);
            return $this->addQuery($nested);
        }
        return $this->addQuery(Nested::create($path));
    }

    /**
     * Add a has_child query.
     *
     * @param callable|string|HasChild|array<string, mixed> $type type string, or full has_child definition
     * @param callable|Query|array<string, mixed>|null $query optional query when first arg is a type string
     * @return $this
     */
    public function hasChild($type, $query = null): static
    {
        if (is_string($type) && $query !== null) {
            $hasChild = HasChild::create();
            $hasChild->type($type);
            $hasChild->query($query);
            return $this->addQuery($hasChild);
        }
        return $this->addQuery(HasChild::create($type));
    }

    /**
     * Add a has_parent query.
     *
     * @param callable|string|HasParent|array<string, mixed> $type parent_type string, or full has_parent definition
     * @param callable|Query|array<string, mixed>|null $query optional query when first arg is a parent_type string
     * @return $this
     */
    public function hasParent($type, $query = null): static
    {
        if (is_string($type) && $query !== null) {
            $hasParent = HasParent::create();
            $hasParent->parentType($type);
            $hasParent->query($query);
            return $this->addQuery($hasParent);
        }
        return $this->addQuery(HasParent::create($type));
    }

    /**
     * Add a parent_id query.
     *
     * @param mixed $parentId
     * @return $this
     */
    public function parentId($parentId): static
    {
        return $this->addQuery(ParentId::create($parentId));
    }
}

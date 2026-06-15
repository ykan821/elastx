<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Shape;

use ElasticKit\DSL\Node;

/**
 * Queries documents that contain a shape field, using a provided shape definition
 * or a pre-indexed shape reference.
 *
 * Only works with the shape field type. Supports spatial relation operators:
 * INTERSECTS, DISJOINT, WITHIN, CONTAINS.
 */
class Shape extends Node
{
    protected string $_key = 'shape';

    protected bool $_fieldKeyed = true;

    /**
     * Inline shape definition. Contains the shape type and coordinates.
     *
     * @param mixed $shape
     * @return static
     */
    public function shape($shape): static
    {
        return $this->addProperty('shape', $shape);
    }

    /**
     * Spatial relation operator to use at search time.
     * Valid values: INTERSECTS (default), DISJOINT, WITHIN, CONTAINS.
     *
     * @param string $relation
     * @return static
     */
    public function relation(string $relation): static
    {
        return $this->addProperty('relation', $relation);
    }

    /**
     * Reference to a pre-indexed shape. Contains id, index, path, and routing fields
     * to identify the shape document in another index.
     *
     * @param mixed $indexedShape
     * @return static
     */
    public function indexedShape($indexedShape): static
    {
        return $this->addProperty('indexed_shape', $indexedShape);
    }
}

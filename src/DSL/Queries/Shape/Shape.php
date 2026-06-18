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
     * @param mixed $value
     * @return static
     */
    public function shape($value): static
    {
        return $this->addProperty('shape', $value);
    }

    /**
     * Spatial relation operator to use at search time.
     * Valid values: INTERSECTS (default), DISJOINT, WITHIN, CONTAINS.
     *
     * @param string $value
     * @return static
     */
    public function relation(string $value): static
    {
        return $this->addProperty('relation', $value);
    }

    /**
     * Reference to a pre-indexed shape. Contains id, index, path, and routing fields
     * to identify the shape document in another index.
     *
     * @param mixed $value
     * @return static
     */
    public function indexedShape($value): static
    {
        return $this->addProperty('indexed_shape', $value);
    }
}

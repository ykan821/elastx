<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Geo;

use ElasticKit\DSL\Node;

/**
 * Filter documents indexed using the geo_shape or geo_point type.
 *
 * Requires the geo_shape mapping or the geo_point mapping.
 * The query supports two ways of defining the query shape, either by providing
 * a whole shape definition, or by referencing the name of a shape pre-indexed
 * in another index.
 */
class GeoShape extends Node
{
    protected string $_key = 'geo_shape';

    protected bool $_fieldKeyed = true;

    /**
     * Inline shape definition using GeoJSON or Well-Known Text (WKT).
     * Contains the shape type and coordinates.
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

    /**
     * When set to true, the ignore_unmapped option will ignore an unmapped field
     * and will not match any documents for this query. Defaults to false.
     *
     * @param bool $ignoreUnmapped
     * @return static
     */
    public function ignoreUnmapped(bool $ignoreUnmapped): static
    {
        return $this->addProperty('ignore_unmapped', $ignoreUnmapped);
    }
}

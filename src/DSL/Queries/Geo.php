<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\Geo\GeoBoundingBox;
use ElasticKit\DSL\Queries\Geo\GeoDistance;
use ElasticKit\DSL\Queries\Geo\GeoGrid;
use ElasticKit\DSL\Queries\Geo\GeoPolygon;
use ElasticKit\DSL\Queries\Geo\GeoShape;

/**
 * Shortcut methods for geo query types.
 */
trait Geo
{
    /**
     * Add a geo_bounding_box query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function geoBoundingBox($field, $value = null): static
    {
        return $this->addQuery(GeoBoundingBox::create($field, $value));
    }

    /**
     * Add a geo_distance query.
     *
     * @example $query->geoDistance(function (GeoDistance $g) { $g->distance('200km') })
     *
     * @param callable|GeoDistance|array<string, mixed> $value
     * @return $this
     */
    public function geoDistance($value = null): static
    {
        return $this->addQuery(GeoDistance::create($value));
    }

    /**
     * Add a geo_grid query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function geoGrid($field, $value = null): static
    {
        return $this->addQuery(GeoGrid::create($field, $value));
    }

    /**
     * Add a geo_polygon query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function geoPolygon($field, $value = null): static
    {
        return $this->addQuery(GeoPolygon::create($field, $value));
    }

    /**
     * Add a geo_shape query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function geoShape($field, $value = null): static
    {
        return $this->addQuery(GeoShape::create($field, $value));
    }
}

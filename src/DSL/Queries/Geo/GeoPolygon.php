<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Geo;

use ElasticKit\DSL\Node;

/**
 * Returns hits that only fall within a polygon of points.
 *
 * Deprecated in 7.12. Use geo_shape instead where polygons are defined in GeoJSON or Well-Known Text (WKT).
 */
class GeoPolygon extends Node
{
    protected string $_key = 'geo_polygon';

    protected bool $_fieldKeyed = true;

    /**
     * Array of geo points that define the polygon.
     * At least three points are required to form a polygon.
     *
     * @param array<int, array<string, mixed>> $value
     * @return static
     */
    public function points(array $value): static
    {
        return $this->addProperty('points', $value);
    }

    /**
     * Set to IGNORE_MALFORMED to accept geo points with invalid latitude or longitude,
     * set to COERCE to try and infer correct latitude or longitude, or STRICT (default).
     *
     * @param string $value
     * @return static
     */
    public function validationMethod(string $value): static
    {
        return $this->addProperty('validation_method', $value);
    }

    /**
     * When set to true, the ignore_unmapped option will ignore an unmapped field
     * and will not match any documents for this query. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ignoreUnmapped(bool $value): static
    {
        return $this->addProperty('ignore_unmapped', $value);
    }
}

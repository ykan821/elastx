<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Geo;

use ElasticKit\DSL\Node;

/**
 * Matches geo_point and geo_shape values within a given distance of a geopoint.
 *
 * Unlike other geo types where the field name is the sole top-level key
 * (e.g. {"geo_bounding_box": {"pin.location": {...}}}), geo_distance mixes
 * the field name alongside other parameters like distance and distance_type
 * (e.g. {"geo_distance": {"distance": "200km", "pin.location": {...}}}).
 * Therefore this class does not use _fieldKeyed — the field is set
 * via location() as a regular property.
 */
class GeoDistance extends Node
{
    protected string $_key = 'geo_distance';

    /**
     * The radius of the circle centred on the specified location.
     * Points which fall into this circle are considered to be matches.
     * The distance can be specified in various units.
     *
     * @param string $value
     * @return static
     */
    public function distance(string $value): static
    {
        return $this->addProperty('distance', $value);
    }

    /**
     * The central geo point used to compute the distance.
     * The field name is provided as the first argument and the location value as the second.
     *
     * @param string $field
     * @param mixed $location
     * @return static
     */
    public function location(string $field, $location): static
    {
        return $this->addProperty($field, $location);
    }

    /**
     * How to compute the distance. Can either be arc (default) or
     * plane (faster, but inaccurate on long distances and close to the poles).
     *
     * @param string $value
     * @return static
     */
    public function distanceType(string $value): static
    {
        return $this->addProperty('distance_type', $value);
    }

    /**
     * Set to IGNORE_MALFORMED to accept geo points with invalid latitude or longitude,
     * set to COERCE to additionally try and infer correct coordinates. Defaults to STRICT.
     *
     * @param string $value
     * @return static
     */
    public function validationMethod(string $value): static
    {
        return $this->addProperty('validation_method', $value);
    }
}

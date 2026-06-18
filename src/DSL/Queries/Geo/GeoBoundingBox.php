<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Geo;

use ElasticKit\DSL\Node;

/**
 * Matches geo_point and geo_shape values that intersect a bounding box.
 *
 * To define the box, provide geopoint values for two opposite corners.
 */
class GeoBoundingBox extends Node
{
    protected string $_key = 'geo_bounding_box';

    protected bool $_fieldKeyed = true;

    /**
     * Top-left corner of the bounding box.
     *
     * @param mixed $value
     * @return static
     */
    public function topLeft($value): static
    {
        return $this->addProperty('top_left', $value);
    }

    /**
     * Bottom-right corner of the bounding box.
     *
     * @param mixed $value
     * @return static
     */
    public function bottomRight($value): static
    {
        return $this->addProperty('bottom_right', $value);
    }

    /**
     * Top latitude of the bounding box. Can be used instead of topLeft/bottomRight pairs to set values separately.
     *
     * @param float $value
     * @return static
     */
    public function top(float $value): static
    {
        return $this->addProperty('top', $value);
    }

    /**
     * Left longitude of the bounding box. Can be used instead of topLeft/bottomRight pairs to set values separately.
     *
     * @param float $value
     * @return static
     */
    public function left(float $value): static
    {
        return $this->addProperty('left', $value);
    }

    /**
     * Bottom latitude of the bounding box. Can be used instead of topLeft/bottomRight pairs to set values separately.
     *
     * @param float $value
     * @return static
     */
    public function bottom(float $value): static
    {
        return $this->addProperty('bottom', $value);
    }

    /**
     * Right longitude of the bounding box. Can be used instead of topLeft/bottomRight pairs to set values separately.
     *
     * @param float $value
     * @return static
     */
    public function right(float $value): static
    {
        return $this->addProperty('right', $value);
    }

    /**
     * Bounding box defined as Well-Known Text (WKT) BBOX format.
     *
     * @param string $value
     * @return static
     */
    public function wkt(string $value): static
    {
        return $this->addProperty('wkt', $value);
    }

    /**
     * Top-right corner of the bounding box. Can be used with bottomLeft instead of topLeft/bottomRight.
     *
     * @param mixed $value
     * @return static
     */
    public function topRight($value): static
    {
        return $this->addProperty('top_right', $value);
    }

    /**
     * Bottom-left corner of the bounding box. Can be used with topRight instead of topLeft/bottomRight.
     *
     * @param mixed $value
     * @return static
     */
    public function bottomLeft($value): static
    {
        return $this->addProperty('bottom_left', $value);
    }

    /**
     * Set to IGNORE_MALFORMED to accept geo points with invalid latitude or longitude,
     * set to COERCE to also try to infer correct latitude or longitude. Defaults to STRICT.
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

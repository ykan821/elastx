<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Geo;

use ElasticKit\DSL\Node;

/**
 * Matches geo_point and geo_shape values that intersect a grid cell from a GeoGrid aggregation.
 *
 * The query is designed to match the documents that fall inside a bucket of a geogrid aggregation
 * by providing the key of the bucket. For geohash and geotile grids, the query can be used for
 * geo_point and geo_shape fields. For geo_hex grid, it can only be used for geo_point fields.
 */
class GeoGrid extends Node
{
    protected string $_key = 'geo_grid';

    protected bool $_fieldKeyed = true;

    /**
     * The geohex grid key to match. Only usable with geo_point fields.
     *
     * @param string $value
     * @return static
     */
    public function geohex(string $value): static
    {
        return $this->addProperty('geohex', $value);
    }

    /**
     * The geotile grid key to match (e.g. "6/32/21").
     * Usable with geo_point and geo_shape fields.
     *
     * @param string $value
     * @return static
     */
    public function geotile(string $value): static
    {
        return $this->addProperty('geotile', $value);
    }

    /**
     * The geohash grid key to match (e.g. "u1").
     * Usable with geo_point and geo_shape fields.
     *
     * @param string $value
     * @return static
     */
    public function geohash(string $value): static
    {
        return $this->addProperty('geohash', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound\Functions;

use ElasticKit\DSL\Node;

/**
 * Exponential decay score function, computed based on distance from a numeric, date, or geopoint field origin.
 */
class Exp extends Node
{
    protected bool $_fieldKeyed = true;

    protected string $_key = 'exp';

    /**
     * The point of origin used for calculating distance. Must be a number for numeric fields, date for date fields, and geo point for geo fields.
     *
     * @param mixed $origin
     * @return static
     */
    public function origin($origin): static
    {
        return $this->addProperty('origin', $origin);
    }

    /**
     * Defines the distance from origin + offset at which the computed score will equal the decay parameter.
     *
     * @param mixed $scale
     * @return static
     */
    public function scale($scale): static
    {
        return $this->addProperty('scale', $scale);
    }

    /**
     * If defined, the decay function will only compute for documents with a distance greater than this offset. Defaults to 0.
     *
     * @param mixed $offset
     * @return static
     */
    public function offset($offset): static
    {
        return $this->addProperty('offset', $offset);
    }

    /**
     * Defines how documents are scored at the distance given at scale. Defaults to 0.5.
     *
     * @param float $decay
     * @return static
     */
    public function decay(float $decay): static
    {
        return $this->addProperty('decay', $decay);
    }
}

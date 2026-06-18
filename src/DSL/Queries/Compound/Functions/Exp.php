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
     * @param mixed $value
     * @return static
     */
    public function origin($value): static
    {
        return $this->addProperty('origin', $value);
    }

    /**
     * Defines the distance from origin + offset at which the computed score will equal the decay parameter.
     *
     * @param mixed $value
     * @return static
     */
    public function scale($value): static
    {
        return $this->addProperty('scale', $value);
    }

    /**
     * If defined, the decay function will only compute for documents with a distance greater than this offset. Defaults to 0.
     *
     * @param mixed $value
     * @return static
     */
    public function offset($value): static
    {
        return $this->addProperty('offset', $value);
    }

    /**
     * Defines how documents are scored at the distance given at scale. Defaults to 0.5.
     *
     * @param float $value
     * @return static
     */
    public function decay(float $value): static
    {
        return $this->addProperty('decay', $value);
    }
}

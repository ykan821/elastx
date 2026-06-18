<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Node;

/**
 * Scores documents by distance from an origin point or date.
 */
class DistanceFeature extends Node
{
    protected string $_key = 'distance_feature';

    /**
     * Location or date to use as the origin from which to calculate distance.
     *
     * @param mixed $value
     * @return static
     */
    public function origin($value): static
    {
        return $this->addProperty('origin', $value);
    }

    /**
     * Distance from the origin at which relevance scores receive half of the boost value.
     *
     * @param string $value
     * @return static
     */
    public function pivot(string $value): static
    {
        return $this->addProperty('pivot', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Node;

/**
 * Scores documents based on the value of a rank feature field.
 */
class RankFeature extends Node
{
    protected string $_key = 'rank_feature';

    /**
     * Saturation function to compute the score. Uses point: 2 by default.
     *
     * @param mixed $value
     * @return static
     */
    public function saturation($value): static
    {
        return $this->addProperty('saturation', $value);
    }

    /**
     * Logarithmic function to compute the score. Supports a scaling_factor parameter.
     *
     * @param mixed $value
     * @return static
     */
    public function log($value): static
    {
        return $this->addProperty('log', $value);
    }

    /**
     * Sigmoid function to compute the score. Requires exponent and pivot parameters.
     *
     * @param mixed $value
     * @return static
     */
    public function sigmoid($value): static
    {
        return $this->addProperty('sigmoid', $value);
    }

    /**
     * Linear function to compute the score, producing a linear relation between the feature value and the score.
     *
     * @param mixed $value
     * @return static
     */
    public function linear($value): static
    {
        return $this->addProperty('linear', $value);
    }
}

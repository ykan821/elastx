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
     * @param mixed $saturation
     * @return static
     */
    public function saturation($saturation): static
    {
        return $this->addProperty('saturation', $saturation);
    }

    /**
     * Logarithmic function to compute the score. Supports a scaling_factor parameter.
     *
     * @param mixed $log
     * @return static
     */
    public function log($log): static
    {
        return $this->addProperty('log', $log);
    }

    /**
     * Sigmoid function to compute the score. Requires exponent and pivot parameters.
     *
     * @param mixed $sigmoid
     * @return static
     */
    public function sigmoid($sigmoid): static
    {
        return $this->addProperty('sigmoid', $sigmoid);
    }

    /**
     * Linear function to compute the score, producing a linear relation between the feature value and the score.
     *
     * @param mixed $linear
     * @return static
     */
    public function linear($linear): static
    {
        return $this->addProperty('linear', $linear);
    }
}

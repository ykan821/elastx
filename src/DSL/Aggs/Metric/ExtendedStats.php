<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Metric;

use ElasticKit\DSL\Node;

/**
 * A multi-value metrics aggregation that computes extended stats over numeric values.
 */
class ExtendedStats extends Node
{
    protected string $_key = 'extended_stats';

    /**
     * The value to use when the field is missing.
     *
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }

    /**
     * The script to use for the aggregation.
     *
     * @param string|callable $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', $script);
    }

    /**
     * Number of standard deviations above/below the mean to display.
     *
     * @param int $sigma
     * @return static
     */
    public function sigma(int $sigma): static
    {
        return $this->addProperty('sigma', $sigma);
    }
}

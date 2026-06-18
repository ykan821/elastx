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
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }

    /**
     * The script to use for the aggregation.
     *
     * @param string|callable $value
     * @return static
     */
    public function script($value): static
    {
        return $this->addProperty('script', $value);
    }

    /**
     * Number of standard deviations above/below the mean to display.
     *
     * @param int $value
     * @return static
     */
    public function sigma(int $value): static
    {
        return $this->addProperty('sigma', $value);
    }
}

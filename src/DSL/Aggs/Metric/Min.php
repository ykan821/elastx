<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Metric;

use ElasticKit\DSL\Node;

/**
 * A single-value metrics aggregation that keeps track of the minimum value.
 */
class Min extends Node
{
    protected string $_key = 'min';

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
}

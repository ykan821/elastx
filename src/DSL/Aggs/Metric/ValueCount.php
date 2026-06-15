<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Metric;

use ElasticKit\DSL\Node;

/**
 * A single-value metrics aggregation that counts the number of values extracted from documents.
 */
class ValueCount extends Node
{
    protected string $_key = 'value_count';

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
}

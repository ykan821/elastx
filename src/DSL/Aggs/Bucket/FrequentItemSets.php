<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that finds frequent item sets in a dataset.
 */
class FrequentItemSets extends Node
{
    protected string $_key = 'frequent_item_sets';

    /**
     * Minimum size of an item set.
     *
     * @param int $value
     * @return static
     */
    public function minimumSetSize(int $value): static
    {
        return $this->addProperty('minimum_set_size', $value);
    }

    /**
     * Fields to analyze for frequent item sets.
     *
     * @param array<string> $value
     * @return static
     */
    public function fields(array $value): static
    {
        return $this->addProperty('fields', $value);
    }

    /**
     * Maximum number of item sets to return.
     *
     * @param int $value
     * @return static
     */
    public function size(int $value): static
    {
        return $this->addProperty('size', $value);
    }

    /**
     * Query to filter documents before analysis.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        return $this->addProperty('filter', $value);
    }
}

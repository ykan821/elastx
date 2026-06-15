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
     * @param int $minimumSetSize
     * @return static
     */
    public function minimumSetSize(int $minimumSetSize): static
    {
        return $this->addProperty('minimum_set_size', $minimumSetSize);
    }

    /**
     * Fields to analyze for frequent item sets.
     *
     * @param array<string> $fields
     * @return static
     */
    public function fields(array $fields): static
    {
        return $this->addProperty('fields', $fields);
    }

    /**
     * Maximum number of item sets to return.
     *
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        return $this->addProperty('size', $size);
    }

    /**
     * Query to filter documents before analysis.
     *
     * @param mixed $filter
     * @return static
     */
    public function filter($filter): static
    {
        return $this->addProperty('filter', $filter);
    }
}

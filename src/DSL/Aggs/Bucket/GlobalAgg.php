<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use stdClass;
use ElasticKit\DSL\Node;

/**
 * A single bucket aggregation that defines all documents within the search context.
 */
class GlobalAgg extends Node
{
    protected string $_key = 'global';

    /**
     * Serialize to an Elasticsearch DSL array.
     *
     * @return stdClass
     */
    public function toArray()
    {
        return new stdClass();
    }
}

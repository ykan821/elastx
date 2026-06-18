<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * A single bucket aggregation that limits documents matching a query.
 */
class Filter extends Node
{
    protected string $_key = 'filter';

    /** @var mixed */
    protected $_filter;

    /**
     * The filter query to apply.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        $this->_filter = $value;
        return $this;
    }

    /**
     * Serialize to an Elasticsearch DSL array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return Query::create($this->_filter)->toArray()['query'];
    }
}

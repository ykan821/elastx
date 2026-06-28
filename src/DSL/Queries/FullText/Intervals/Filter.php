<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\FullText\Intervals;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\Script;

/**
 * The filter rule returns intervals based on a query. It can filter intervals
 * by their relationship to other intervals using query objects and scripts.
 */
class Filter extends Node
{
    protected string $_key = 'filter';

    /**
     * Query used to return intervals that follow an
     * interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function after($value): static
    {
        return $this->addProperty('after', Query::create($value));
    }

    /**
     * Query used to return intervals that occur before
     * an interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function before($value): static
    {
        return $this->addProperty('before', Query::create($value));
    }

    /**
     * Query used to return intervals contained by an
     * interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function containedBy($value): static
    {
        return $this->addProperty('contained_by', Query::create($value));
    }

    /**
     * Query used to return intervals that contain an
     * interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function containing($value): static
    {
        return $this->addProperty('containing', Query::create($value));
    }

    /**
     * Query used to return intervals that do not
     * contain an interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function notContaining($value): static
    {
        return $this->addProperty('not_containing', Query::create($value));
    }

    /**
     * Query used to return intervals that overlap
     * with an interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function overlapping($value): static
    {
        return $this->addProperty('overlapping', Query::create($value));
    }

    /**
     * Script used to return matching documents.
     * This script must return a boolean value, true or false. The script can
     * use the interval variable with start, end, and gaps methods.
     *
     * @param mixed $value
     * @return static
     */
    public function script($value): static
    {
        return $this->addProperty('script', Script::create($value));
    }

    /**
     * Query used to return intervals that are not
     * contained by an interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function notContainedBy($value): static
    {
        return $this->addProperty('not_contained_by', Query::create($value));
    }

    /**
     * Query used to return intervals that do not
     * overlap with an interval from the filter rule.
     *
     * @param mixed $value
     * @return static
     */
    public function notOverlapping($value): static
    {
        return $this->addProperty('not_overlapping', Query::create($value));
    }
}

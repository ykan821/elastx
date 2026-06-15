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
     * @param mixed $after
     * @return static
     */
    public function after($after): static
    {
        return $this->addProperty('after', Query::create($after));
    }

    /**
     * Query used to return intervals that occur before
     * an interval from the filter rule.
     *
     * @param mixed $before
     * @return static
     */
    public function before($before): static
    {
        return $this->addProperty('before', Query::create($before));
    }

    /**
     * Query used to return intervals contained by an
     * interval from the filter rule.
     *
     * @param mixed $containedBy
     * @return static
     */
    public function containedBy($containedBy): static
    {
        return $this->addProperty('contained_by', Query::create($containedBy));
    }

    /**
     * Query used to return intervals that contain an
     * interval from the filter rule.
     *
     * @param mixed $containing
     * @return static
     */
    public function containing($containing): static
    {
        return $this->addProperty('containing', Query::create($containing));
    }

    /**
     * Query used to return intervals that do not
     * contain an interval from the filter rule.
     *
     * @param mixed $notContaining
     * @return static
     */
    public function notContaining($notContaining): static
    {
        return $this->addProperty('not_containing', Query::create($notContaining));
    }

    /**
     * Query used to return intervals that overlap
     * with an interval from the filter rule.
     *
     * @param mixed $overlapping
     * @return static
     */
    public function overlapping($overlapping): static
    {
        return $this->addProperty('overlapping', Query::create($overlapping));
    }

    /**
     * Script used to return matching documents.
     * This script must return a boolean value, true or false. The script can
     * use the interval variable with start, end, and gaps methods.
     *
     * @param mixed $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', Script::create($script));
    }

    /**
     * Query used to return intervals that are not
     * contained by an interval from the filter rule.
     *
     * @param mixed $notContainedBy
     * @return static
     */
    public function notContainedBy($notContainedBy): static
    {
        return $this->addProperty('not_contained_by', Query::create($notContainedBy));
    }

    /**
     * Query used to return intervals that do not
     * overlap with an interval from the filter rule.
     *
     * @param mixed $notOverlapping
     * @return static
     */
    public function notOverlapping($notOverlapping): static
    {
        return $this->addProperty('not_overlapping', Query::create($notOverlapping));
    }
}

<?php

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Support\RangeSupport;

class Range extends Node
{
    use RangeSupport;

    protected $_key = 'range';

    protected $_isPropertyField = true;

    /**
     * (Optional) Greater than or equal to.
     *
     * @param mixed $gte
     * @return static
     */
    public function gte($gte)
    {
        return $this->addProperty('gte', $gte);
    }

    /**
     * (Optional) Greater than.
     *
     * @param mixed $gt
     * @return static
     */
    public function gt($gt)
    {
        return $this->addProperty('gt', $gt);
    }

    /**
     * (Optional) Less than or equal to.
     *
     * @param mixed $lte
     * @return static
     */
    public function lte($lte)
    {
        return $this->addProperty('lte', $lte);
    }

    /**
     * (Optional) Less than.
     *
     * @param mixed $lt
     * @return static
     */
    public function lt($lt)
    {
        return $this->addProperty('lt', $lt);
    }

    /**
     * Date format used to convert date values in the query.
     *
     * By default, Elasticsearch uses the date format provided in the <field>'s mapping. This value overrides that mapping format.
     *
     * For valid syntax, see format.
     *
     * If a format or date value is incomplete, the range query replaces any missing components with default values. See Missing date components.
     *
     * @param string $format
     * @return static
     */
    public function format($format)
    {
        return $this->addProperty('format', $format);
    }

    /**
     * Indicates how the range query matches values for range fields. Valid values are:
     *
     * INTERSECTS (Default)
     *    Matches documents with a range field value that intersects the query’s range.
     * CONTAINS
     *    Matches documents with a range field value that entirely contains the query’s range.
     * WITHIN
     *    Matches documents with a range field value entirely within the query’s range.
     *
     * @param string $relation
     * @return static
     */
    public function relation($relation)
    {
        return $this->addProperty('relation', $relation);
    }

    /**
     * Coordinated Universal Time (UTC) offset or IANA time zone used to convert date values in the query to UTC.
     *
     * @param string $timeZone
     * @return static
     */
    public function timeZone($timeZone)
    {
        return $this->addProperty('time_zone', $timeZone);
    }
}

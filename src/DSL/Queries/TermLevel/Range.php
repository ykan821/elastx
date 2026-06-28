<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Support\RangeSupport;

class Range extends Node
{
    use RangeSupport;

    protected string $_key = 'range';

    protected bool $_fieldKeyed = true;

    /**
     * Greater than or equal to.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function gte(string|int|float|bool $value): static
    {
        return $this->addProperty('gte', $value);
    }

    /**
     * Greater than.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function gt(string|int|float|bool $value): static
    {
        return $this->addProperty('gt', $value);
    }

    /**
     * Less than or equal to.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function lte(string|int|float|bool $value): static
    {
        return $this->addProperty('lte', $value);
    }

    /**
     * Less than.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function lt(string|int|float|bool $value): static
    {
        return $this->addProperty('lt', $value);
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
     * @param string $value
     * @return static
     */
    public function format(string $value): static
    {
        return $this->addProperty('format', $value);
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
     * @param string $value
     * @return static
     */
    public function relation(string $value): static
    {
        return $this->addProperty('relation', $value);
    }

    /**
     * Coordinated Universal Time (UTC) offset or IANA time zone used to convert date values in the query to UTC.
     *
     * @param string $value
     * @return static
     */
    public function timeZone(string $value): static
    {
        return $this->addProperty('time_zone', $value);
    }
}

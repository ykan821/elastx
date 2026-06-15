<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs;

use ElasticKit\DSL\Aggs\Metric\Avg;
use ElasticKit\DSL\Aggs\Metric\Cardinality;
use ElasticKit\DSL\Aggs\Metric\ExtendedStats;
use ElasticKit\DSL\Aggs\Metric\Max;
use ElasticKit\DSL\Aggs\Metric\Min;
use ElasticKit\DSL\Aggs\Metric\Stats;
use ElasticKit\DSL\Aggs\Metric\Sum;
use ElasticKit\DSL\Aggs\Metric\ValueCount;

trait Metric
{
    /**
     * Computes the average of numeric values from a field.
     *
     * @param mixed $params
     * @return static
     */
    public function avg($params)
    {
        return $this->node(Avg::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Computes the sum of numeric values from a field.
     *
     * @param mixed $params
     * @return static
     */
    public function sum($params)
    {
        return $this->node(Sum::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Computes the minimum value from a field.
     *
     * @param mixed $params
     * @return static
     */
    public function min($params)
    {
        return $this->node(Min::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Computes the maximum value from a field.
     *
     * @param mixed $params
     * @return static
     */
    public function max($params)
    {
        return $this->node(Max::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Counts the number of distinct values in a field.
     *
     * @param mixed $params
     * @return static
     */
    public function cardinality($params)
    {
        return $this->node(Cardinality::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Counts the number of values in a field, including duplicates.
     *
     * @param mixed $params
     * @return static
     */
    public function valueCount($params)
    {
        return $this->node(ValueCount::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Computes count, min, max, avg, and sum stats from a field in one request.
     *
     * @param mixed $params
     * @return static
     */
    public function stats($params)
    {
        return $this->node(Stats::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Computes extended statistics (stats plus stddev, variance, std error) from a field.
     *
     * @param mixed $params
     * @return static
     */
    public function extendedStats($params)
    {
        return $this->node(ExtendedStats::create(is_string($params) ? ['field' => $params] : $params));
    }
}

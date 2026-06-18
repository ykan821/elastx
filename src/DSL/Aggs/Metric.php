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
     * @param mixed $value
     * @return static
     */
    public function avg($value): static
    {
        return $this->node(Avg::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Computes the sum of numeric values from a field.
     *
     * @param mixed $value
     * @return static
     */
    public function sum($value): static
    {
        return $this->node(Sum::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Computes the minimum value from a field.
     *
     * @param mixed $value
     * @return static
     */
    public function min($value): static
    {
        return $this->node(Min::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Computes the maximum value from a field.
     *
     * @param mixed $value
     * @return static
     */
    public function max($value): static
    {
        return $this->node(Max::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Counts the number of distinct values in a field.
     *
     * @param mixed $value
     * @return static
     */
    public function cardinality($value): static
    {
        return $this->node(Cardinality::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Counts the number of values in a field, including duplicates.
     *
     * @param mixed $value
     * @return static
     */
    public function valueCount($value): static
    {
        return $this->node(ValueCount::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Computes count, min, max, avg, and sum stats from a field in one request.
     *
     * @param mixed $value
     * @return static
     */
    public function stats($value): static
    {
        return $this->node(Stats::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Computes extended statistics (stats plus stddev, variance, std error) from a field.
     *
     * @param mixed $value
     * @return static
     */
    public function extendedStats($value): static
    {
        return $this->node(ExtendedStats::create(is_string($value) ? ['field' => $value] : $value));
    }
}

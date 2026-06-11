<?php

namespace ElasticKit\Index;

/**
 * Shortcut methods for common metric aggregations on Search.
 */
trait AggregationShortcut
{
    /**
     * Return the maximum value of a field.
     *
     * @param string $field
     * @return float|null
     */
    public function max($field)
    {
        return $this->aggregateScalar('max', $field);
    }

    /**
     * Return the minimum value of a field.
     *
     * @param string $field
     * @return float|null
     */
    public function min($field)
    {
        return $this->aggregateScalar('min', $field);
    }

    /**
     * Return the average value of a field.
     *
     * @param string $field
     * @return float|null
     */
    public function avg($field)
    {
        return $this->aggregateScalar('avg', $field);
    }

    /**
     * Return the sum of a field.
     *
     * @param string $field
     * @return float|null
     */
    public function sum($field)
    {
        return $this->aggregateScalar('sum', $field);
    }

    /**
     * Execute a metric aggregation and return the scalar value.
     *
     * @param string $type
     * @param string $field
     * @return float|null
     */
    private function aggregateScalar($type, $field)
    {
        $saved = $this->query;
        $this->query = clone $this->query;
        $this->query->size(0);
        $this->query->aggs('__scalar', [$type => ['field' => $field]]);
        try {
            $response = $this->doSearch($type);
        } finally {
            $this->query = $saved;
        }

        return $response['aggregations']['__scalar']['value'] ?? null;
    }
}

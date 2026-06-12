<?php

namespace ElasticKit\Index;

/**
 * Shortcut methods for common metric aggregations on Search.
 */
trait StatsSupport
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
     * Return stats (count, min, max, avg, sum) for a field in a single request.
     *
     * @param string $field
     * @return array{count: int, min: float|null, max: float|null, avg: float|null, sum: float|null}|null
     */
    public function stats($field)
    {
        $saved = $this->query;
        $this->query = clone $this->query;
        $this->query->size(0);
        $this->query->aggs('__stats', ['stats' => ['field' => $field]]);
        try {
            $response = $this->doSearch('stats');
        } finally {
            $this->query = $saved;
        }

        $raw = $response['aggregations']['__stats'] ?? null;
        if ($raw === null) {
            return null;
        }

        return [
            'count' => $raw['count'] ?? 0,
            'min'   => $raw['min'] ?? null,
            'max'   => $raw['max'] ?? null,
            'avg'   => $raw['avg'] ?? null,
            'sum'   => $raw['sum'] ?? null,
        ];
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

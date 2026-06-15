<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs;

use ElasticKit\DSL\Aggs\Pipeline\AvgBucket;
use ElasticKit\DSL\Aggs\Pipeline\BucketScript;
use ElasticKit\DSL\Aggs\Pipeline\CumulativeSum;
use ElasticKit\DSL\Aggs\Pipeline\Derivative;
use ElasticKit\DSL\Aggs\Pipeline\MaxBucket;
use ElasticKit\DSL\Aggs\Pipeline\MinBucket;
use ElasticKit\DSL\Aggs\Pipeline\StatsBucket;
use ElasticKit\DSL\Aggs\Pipeline\SumBucket;

trait Pipeline
{
    /**
     * Computes the average of a metric across sibling aggregation buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function avgBucket($params)
    {
        return $this->node(AvgBucket::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Computes the sum of a metric across sibling aggregation buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function sumBucket($params)
    {
        return $this->node(SumBucket::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Finds the bucket with the maximum value of a metric across sibling buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function maxBucket($params)
    {
        return $this->node(MaxBucket::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Finds the bucket with the minimum value of a metric across sibling buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function minBucket($params)
    {
        return $this->node(MinBucket::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Computes count, min, max, avg, and sum stats across sibling aggregation buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function statsBucket($params)
    {
        return $this->node(StatsBucket::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Computes a cumulative running sum of a metric across parent histogram buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function cumulativeSum($params)
    {
        return $this->node(CumulativeSum::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Computes the derivative of a metric between consecutive parent histogram buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function derivative($params)
    {
        return $this->node(Derivative::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }

    /**
     * Runs a custom script to compute values from multiple bucket metrics.
     *
     * @param mixed $params
     * @return static
     */
    public function bucketScript($params)
    {
        return $this->node(BucketScript::create(is_string($params) ? ['buckets_path' => $params] : $params));
    }
}

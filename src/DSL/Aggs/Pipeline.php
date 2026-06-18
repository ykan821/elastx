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
     * @param mixed $value
     * @return static
     */
    public function avgBucket($value): static
    {
        return $this->node(AvgBucket::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Computes the sum of a metric across sibling aggregation buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function sumBucket($value): static
    {
        return $this->node(SumBucket::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Finds the bucket with the maximum value of a metric across sibling buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function maxBucket($value): static
    {
        return $this->node(MaxBucket::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Finds the bucket with the minimum value of a metric across sibling buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function minBucket($value): static
    {
        return $this->node(MinBucket::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Computes count, min, max, avg, and sum stats across sibling aggregation buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function statsBucket($value): static
    {
        return $this->node(StatsBucket::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Computes a cumulative running sum of a metric across parent histogram buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function cumulativeSum($value): static
    {
        return $this->node(CumulativeSum::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Computes the derivative of a metric between consecutive parent histogram buckets.
     *
     * @param mixed $value
     * @return static
     */
    public function derivative($value): static
    {
        return $this->node(Derivative::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }

    /**
     * Runs a custom script to compute values from multiple bucket metrics.
     *
     * @param mixed $value
     * @return static
     */
    public function bucketScript($value): static
    {
        return $this->node(BucketScript::create(is_string($value) ? ['buckets_path' => $value] : $value));
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

/**
 * A bucket aggregation that limits any child aggregations to a random sample of documents.
 */
class RandomSampler extends Node
{
    protected string $_key = 'random_sampler';

    /**
     * Probability that a document is included in the sample (between 0 and 1).
     *
     * @param float $value
     * @return static
     */
    public function probability(float $value): static
    {
        return $this->addProperty('probability', $value);
    }

    /**
     * Seed for the random number generator to produce repeatable samples.
     *
     * @param int $value
     * @return static
     */
    public function seed(int $value): static
    {
        return $this->addProperty('seed', $value);
    }
}

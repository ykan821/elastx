<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Query;
use stdClass;

/**
 * Performs a k-nearest neighbor (kNN) search on a dense_vector field.
 *
 * @phpstan-consistent-constructor
 */
class Knn extends Node
{
    protected string $_key = 'knn';

    /**
     * The query vector to search for.
     *
     * @param array<int|float> $value
     * @return static
     */
    public function queryVector(array $value): static
    {
        return $this->addProperty('query_vector', $value);
    }

    /**
     * Number of nearest neighbors to return as top hits.
     * Defaults to the search request's size.
     *
     * @param int $value
     * @return static
     */
    public function k(int $value): static
    {
        return $this->addProperty('k', $value);
    }

    /**
     * Number of candidates to evaluate per shard.
     * Defaults to max(k * 4, 50).
     *
     * @param int $value
     * @return static
     */
    public function numCandidates(int $value): static
    {
        return $this->addProperty('num_candidates', $value);
    }

    /**
     * Minimum similarity threshold for a vector to be
     * considered a match.
     *
     * @param float $value
     * @return static
     */
    public function similarity(float $value): static
    {
        return $this->addProperty('similarity', $value);
    }

    /**
     * Pre-filter applied during kNN search. Accepts a closure,
     * array, or Query object.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        return $this->addProperty('filter', Query::create($value));
    }

    /**
     * Inner hits configuration for nested kNN search.
     *
     * @param mixed $value
     * @return static
     */
    public function innerHits($value): static
    {
        return $this->addProperty('inner_hits', $value);
    }

    /**
     * Rescore vector configuration for quantized vector rescoring.
     *
     * @param array<string, mixed> $value
     * @return static
     */
    public function rescoreVector(array $value): static
    {
        return $this->addProperty('rescore_vector', $value);
    }

    public function toArray()
    {
        $result = parent::toArray();
        if (isset($result['filter']) && $result['filter'] instanceof Query) {
            $result['filter'] = $result['filter']->toArray()['query'] ?? new stdClass();
        }
        return $result;
    }
}

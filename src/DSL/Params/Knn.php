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
     * @param array<int|float> $vector
     * @return static
     */
    public function queryVector(array $vector): static
    {
        return $this->addProperty('query_vector', $vector);
    }

    /**
     * Number of nearest neighbors to return as top hits.
     * Defaults to the search request's size.
     *
     * @param int $k
     * @return static
     */
    public function k(int $k): static
    {
        return $this->addProperty('k', $k);
    }

    /**
     * Number of candidates to evaluate per shard.
     * Defaults to max(k * 4, 50).
     *
     * @param int $num
     * @return static
     */
    public function numCandidates(int $num): static
    {
        return $this->addProperty('num_candidates', $num);
    }

    /**
     * Minimum similarity threshold for a vector to be
     * considered a match.
     *
     * @param float $similarity
     * @return static
     */
    public function similarity(float $similarity): static
    {
        return $this->addProperty('similarity', $similarity);
    }

    /**
     * Boost value for the kNN score.
     *
     * @param float $boost
     * @return static
     */
    public function boost($boost): static
    {
        return $this->addProperty('boost', $boost);
    }

    /**
     * Pre-filter applied during kNN search. Accepts a closure,
     * array, or Query object.
     *
     * @param mixed $filter
     * @return static
     */
    public function filter($filter): static
    {
        return $this->addProperty('filter', Query::create($filter));
    }

    /**
     * Inner hits configuration for nested kNN search.
     *
     * @param mixed $innerHits
     * @return static
     */
    public function innerHits($innerHits): static
    {
        return $this->addProperty('inner_hits', $innerHits);
    }

    /**
     * Rescore vector configuration for quantized vector rescoring.
     *
     * @param array<string, mixed> $rescoreVector
     * @return static
     */
    public function rescoreVector(array $rescoreVector): static
    {
        return $this->addProperty('rescore_vector', $rescoreVector);
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

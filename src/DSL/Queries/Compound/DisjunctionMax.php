<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound;

use ElasticKit\DSL\Support\ClausesSupport;
use ElasticKit\DSL\Node;

/**
 * Returns documents matching one or more wrapped queries, called query clauses or clauses.
 */
class DisjunctionMax extends Node
{
    use ClausesSupport;

    protected string $_key = 'dis_max';

    /**
     * Contains one or more query clauses. Returned documents must match one or more of these queries. If a document matches multiple queries, Elasticsearch uses the highest relevance score.
     * Supports multiple calls to incrementally build.
     *
     * @param mixed $queries
     * @return static
     */
    public function queries($queries): static
    {
        return $this->addClause('queries', $queries);
    }

    /**
     * Floating point number between 0 and 1.0 used to increase the relevance scores of documents matching multiple query clauses. Defaults to 0.0.
     *
     * @param float $tieBreaker
     * @return static
     */
    public function tieBreaker(float $tieBreaker): static
    {
        return $this->addProperty('tie_breaker', $tieBreaker);
    }
}

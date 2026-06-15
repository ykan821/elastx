<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Query;

/**
 * Returns documents matching a positive query while reducing the relevance score of documents that also match a negative query.
 */
class Boosting extends Node
{
    protected string $_key = 'boosting';

    /**
     * Query you wish to run. Any returned documents must match this query.
     *
     * @param mixed $positive
     * @return static
     */
    public function positive($positive): static
    {
        return $this->addProperty('positive', Query::create($positive));
    }

    /**
     * Query used to decrease the relevance score of matching documents.
     *
     * @param mixed $negative
     * @return static
     */
    public function negative($negative): static
    {
        return $this->addProperty('negative', Query::create($negative));
    }

    /**
     * Floating point number between 0 and 1.0 used to decrease the relevance scores of documents matching the negative query.
     *
     * @param float $negativeBoost
     * @return static
     */
    public function negativeBoost(float $negativeBoost): static
    {
        return $this->addProperty('negative_boost', $negativeBoost);
    }
}

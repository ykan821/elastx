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
     * @param mixed $value
     * @return static
     */
    public function positive($value): static
    {
        return $this->addProperty('positive', Query::create($value));
    }

    /**
     * Query used to decrease the relevance score of matching documents.
     *
     * @param mixed $value
     * @return static
     */
    public function negative($value): static
    {
        return $this->addProperty('negative', Query::create($value));
    }

    /**
     * Floating point number between 0 and 1.0 used to decrease the relevance scores of documents matching the negative query.
     *
     * @param float $value
     * @return static
     */
    public function negativeBoost(float $value): static
    {
        return $this->addProperty('negative_boost', $value);
    }
}

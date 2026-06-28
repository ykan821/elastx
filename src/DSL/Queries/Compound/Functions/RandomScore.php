<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound\Functions;

use ElasticKit\DSL\Node;

/**
 * Generates uniformly distributed random scores from 0 up to but not including 1.
 */
class RandomScore extends Node
{
    protected string $_key = 'random_score';

    /**
     * Seed value for reproducible random scores.
     *
     * @param mixed $value
     * @return static
     */
    public function seed($value): static
    {
        return $this->addProperty('seed', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->_properties ?: (object)[];
    }
}

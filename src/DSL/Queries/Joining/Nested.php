<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Joining;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Wraps another query to search nested field objects as if they were indexed as separate documents.
 *
 * If an object matches the search, the nested query returns the root parent document.
 */
class Nested extends Node
{
    protected string $_key = 'nested';

    /**
     * Create an instance from various input formats.
     *
     * - String: creates instance with the path set (shorthand).
     * - Other: delegates to parent::create().
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public static function create($field = null, $value = null): static
    {
        if ($value === null && is_string($field)) {
            return (new static())->path($field);
        }
        return parent::create($field, $value);
    }

    /**
     * Path to the nested object you wish to search.
     *
     * @param string $value
     * @return static
     */
    public function path(string $value): static
    {
        return $this->addProperty('path', $value);
    }

    /**
     * Query you wish to run on nested objects in the path.
     * If an object matches the search, the nested query returns the root parent document.
     *
     * @param mixed $value
     * @return static
     */
    public function query($value): static
    {
        return $this->addProperty('query', Query::create($value));
    }

    /**
     * Indicates how scores for matching child objects affect the root parent
     * document's relevance score. Valid values: avg (default), max, min, none, sum.
     *
     * @param string $value
     * @return static
     */
    public function scoreMode(string $value): static
    {
        return $this->addProperty('score_mode', $value);
    }

    /**
     * Indicates whether to ignore an unmapped path and not return any
     * documents instead of an error. Defaults to false.
     *
     * @param bool $value
     * @return static
     */
    public function ignoreUnmapped(bool $value): static
    {
        return $this->addProperty('ignore_unmapped', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use ElasticKit\DSL\Node;

/**
 * Collapses search results by a field value.
 *
 * @phpstan-consistent-constructor
 */
class Collapse extends Node
{
    protected string $_key = 'collapse';

    /**
     * Create an instance from various input formats.
     *
     * - String: creates instance with the collapse field (shorthand for field()).
     * - Other: delegates to parent::create().
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public static function create($field = null, $value = null): static
    {
        if ($value === null && is_string($field)) {
            return (new static())->field($field);
        }
        return parent::create($field, $value);
    }

    /**
     * Expand each collapsed top hit with the inner_hits option.
     *
     * @param string $name
     * @param mixed $hits
     * @return static
     */
    public function innerHits(string $name, $hits = null): static
    {
        return $this->addProperty('inner_hits', ['name' => $name] + (array)$hits);
    }

    /**
     * Maximum number of concurrent group searches.
     *
     * @param int $max
     * @return static
     */
    public function maxConcurrentGroupSearches(int $max): static
    {
        return $this->addProperty('max_concurrent_group_searches', $max);
    }
}

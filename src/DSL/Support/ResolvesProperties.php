<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Support;

use Closure;
use ElasticKit\DSL\Agg;
use ElasticKit\DSL\Node;
use ElasticKit\DSL\Query;

/**
 * Shared property resolution for Node and Agg.
 *
 * Recursively resolves nested Query, Node, and Agg instances
 * inside a properties array into their DSL representations.
 */
trait ResolvesProperties
{
    /**
     * Resolve nested Query, Node, and Agg instances in a properties array.
     *
     * @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    protected function resolveProperties(array $properties): array
    {
        foreach ($properties as $key => $property) {
            if ($property instanceof Query) {
                $properties[$key] = $property->toArray()['query'] ?? null;
            } elseif ($property instanceof Agg) {
                $properties[$key] = $property->toArray();
            } elseif ($property instanceof Node) {
                $properties[$key] = $property->toArray();
            } elseif ($property instanceof Closure) {
                $properties[$key] = Query::create($property)->toArray()['query'] ?? null;
            } elseif (is_array($property)) {
                $properties[$key] = $this->resolveProperties($property);
            }
        }
        return array_filter($properties, fn ($v) => $v !== null);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Support;

use BadMethodCallException;
use ElasticKit\DSL\Agg;
use RuntimeException;

/**
 * Shared aggregation registration logic for Query and Agg.
 *
 * Both classes accept the same polymorphic inputs (Agg instance, array, closure)
 * and enforce the same duplicate-alias rule; only the storage array differs.
 */
trait RegistersAgg
{
    /**
     * Register an aggregation under an alias into the given store.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity) flat type-dispatch on polymorphic input; each branch is simple
     *
     * @param mixed $alias aggregation alias (string), Agg instance (when passed as the only argument), or null
     * @param mixed $aggs Agg instance, array, closure, or null
     * @param array<string, Agg> $store the target aggregation store (by reference)
     * @return static
     *
     * @throws BadMethodCallException if the alias is empty or the definition is invalid
     * @throws RuntimeException if the alias already exists in the store
     */
    protected function registerAgg($alias, $aggs, array &$store): static
    {
        if ($aggs === null && !is_string($alias)) {
            $aggs = $alias;
            $alias = null;
        }

        if ($aggs instanceof Agg) {
            $key = $alias ?? $aggs->getAlias();
            if ($key === null || $key === '') {
                throw new BadMethodCallException('aggs() requires a non-empty alias.');
            }
            $aggs->alias($key);
            if (isset($store[$key])) {
                throw new RuntimeException(sprintf('Duplicate aggregation alias "%s".', $key));
            }
            $store[$key] = $aggs;
            return $this;
        }

        if ($alias === null || $alias === '') {
            throw new BadMethodCallException(
                'aggs() requires a non-empty alias. Use aggs("name", $definition).'
            );
        }

        if (is_array($aggs)) {
            $childAgg = Agg::create($aggs);
            $childAgg->alias($alias);
            if (isset($store[$alias])) {
                throw new RuntimeException(sprintf('Duplicate aggregation alias "%s".', $alias));
            }
            $store[$alias] = $childAgg;
            return $this;
        }

        if (isset($store[$alias])) {
            throw new RuntimeException(sprintf('Duplicate aggregation alias "%s".', $alias));
        }

        $store[$alias] = new Agg();
        $store[$alias]->alias($alias);

        if ($aggs instanceof \Closure) {
            $aggs($store[$alias]);
            return $this;
        }

        throw new BadMethodCallException(
            sprintf('aggs("%s", ...) requires a closure, array, or Agg instance as the definition.', $alias)
        );
    }
}

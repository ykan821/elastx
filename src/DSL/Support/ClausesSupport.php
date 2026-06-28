<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Support;

use Closure;
use ElasticKit\DSL\Query;

/**
 * Provides clause accumulation for compound query nodes.
 *
 * Each clause key holds a Query(multi=true) instance.
 * Closures receive the same Query object across calls.
 */
trait ClausesSupport
{
    /**
     * Append a clause for the given property.
     *
     * @param string $key Property name (e.g. 'must', 'clauses', 'queries')
     * @param mixed $clause
     * @return static
     */
    protected function addClause(string $key, $clause): static
    {
        if (!isset($this->_properties[$key])) {
            $this->_properties[$key] = (new Query())->multi(true);
        }
        $target = $this->_properties[$key];
        if ($clause instanceof Closure) {
            $clause($target);
        } elseif (is_array($clause) && array_is_list($clause)) {
            foreach ($clause as $item) {
                $target->addQuery($item);
            }
        } else {
            $target->addQuery($clause);
        }
        return $this;
    }

    /**
     * Handle two-argument construction: route through routeKeyValueClause,
     * otherwise fall back to the default field-value handling.
     *
     * @param mixed $field
     * @param mixed $value
     */
    protected function fromKeyValue($field, $value): void
    {
        if ($this->routeKeyValueClause($field, $value)) {
            return;
        }
        parent::fromKeyValue($field, $value);
    }

    /**
     * Route a field-value pair to its setter when the DSL key maps to a method
     * (snake_case → camelCase). Powers both array input (fromArrayProperties)
     * and two-arg construction (new Boolean('must', $query)).
     *
     * Whether a clause accumulates (addClause) or overwrites (addProperty) is
     * decided inside each setter, so no clause-key declaration is needed.
     *
     * @param mixed $field
     * @param mixed $value
     */
    protected function routeKeyValueClause($field, $value): bool
    {
        if (!is_string($field)) {
            return false;
        }
        $method = lcfirst(str_replace('_', '', ucwords($field, '_')));
        if (method_exists($this, $method)) {
            $this->$method($value);
            return true;
        }
        return false;
    }

    /**
     * Route keys with a setter through it; everything else is a raw property.
     *
     * @param array<string, mixed> $field
     */
    protected function fromArrayProperties(array $field): void
    {
        foreach ($field as $key => $val) {
            if (!$this->routeKeyValueClause($key, $val)) {
                $this->addProperty($key, $val);
            }
        }
    }
}

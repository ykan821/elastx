<?php

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
    protected function addClause(string $key, $clause)
    {
        if (!isset($this->_properties[$key])) {
            $this->_properties[$key] = (new Query())->multi(true);
        }
        $target = $this->_properties[$key];
        if ($clause instanceof Closure) {
            $clause($target);
        } else {
            $target->addQuery($clause);
        }
        return $this;
    }
}

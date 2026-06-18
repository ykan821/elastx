<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\Shape\Shape as QShape;

/**
 * Shortcut methods for shape query types.
 */
trait Shape
{
    /**
     * Add a shape query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function shape($field, $value = null): static
    {
        return $this->addQuery(QShape::create($field, $value));
    }
}

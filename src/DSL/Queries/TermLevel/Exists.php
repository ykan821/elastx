<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

/**
 * Returns documents that contain an indexed value for a field.
 */
class Exists extends Node
{
    protected string $_key = 'exists';

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function __construct($field = null, $value = null)
    {
        parent::__construct(is_string($field) ? ['field' => $field] : $field, $value);
    }
}

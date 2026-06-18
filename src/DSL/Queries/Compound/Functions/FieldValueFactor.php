<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound\Functions;

use ElasticKit\DSL\Node;

class FieldValueFactor extends Node
{
    protected string $_key = 'field_value_factor';

    /**
     * Optional factor to multiply the field value with, defaults to 1.
     *
     * @param float $value
     * @return static
     */
    public function factor(float $value): static
    {
        return $this->addProperty('factor', $value);
    }

    /**
     * Modifier to apply to the field value, can be one of: none, log, log1p, log2p, ln, ln1p, ln2p, square, sqrt, or reciprocal. Defaults to none.
     *
     * @param string $value
     * @return static
     */
    public function modifier(string $value): static
    {
        return $this->addProperty('modifier', $value);
    }

    /**
     * Value used if the document doesn’t have that field. The modifier and factor are still applied to it as though it were read from the document.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function missing(string|int|float|bool $value): static
    {
        return $this->addProperty('missing', $value);
    }
}

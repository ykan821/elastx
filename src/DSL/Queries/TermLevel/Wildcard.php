<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

class Wildcard extends Node
{
    protected string $_key = 'wildcard';

    protected bool $_fieldKeyed = true;

    /**
     * Allows case insensitive matching of the pattern with the indexed field values when set to true. Default is false which means the case sensitivity of matching depends on the underlying field’s mapping.
     *
     * @param bool $value
     * @return static
     * @version 7.10.0
     */
    public function caseInsensitive(bool $value): static
    {
        return $this->addProperty('case_insensitive', $value);
    }

    /**
     * Method used to rewrite the query. For valid values and more information, see the rewrite parameter.
     *
     * @param string $value
     * @return static
     */
    public function rewrite(string $value): static
    {
        return $this->addProperty('rewrite', $value);
    }

    /**
     * Wildcard pattern for terms you wish to find in the provided <field>.
     *
     * This parameter supports two wildcard operators:
     *
     * ?, which matches any single character
     *
     * *, which can match zero or more characters, including an empty one
     *
     * @param string $value
     * @return static
     */
    public function value(string $value): static
    {
        return $this->addProperty('value', $value);
    }

    /**
     * An alias for the value parameter. If you specify both value and wildcard, the query uses the last one in the request body.
     *
     * @param string $value
     * @return static
     */
    public function wildcard(string $value): static
    {
        return $this->addProperty('wildcard', $value);
    }
}

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
     * @param bool $caseInsensitive
     * @return static
     * @version 7.10.0
     */
    public function caseInsensitive(bool $caseInsensitive): static
    {
        return $this->addProperty('case_insensitive', $caseInsensitive);
    }

    /**
     * Method used to rewrite the query. For valid values and more information, see the rewrite parameter.
     *
     * @param string $rewrite
     * @return static
     */
    public function rewrite(string $rewrite): static
    {
        return $this->addProperty('rewrite', $rewrite);
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
     * @param string $wildcard
     * @return static
     */
    public function wildcard(string $wildcard): static
    {
        return $this->addProperty('wildcard', $wildcard);
    }
}

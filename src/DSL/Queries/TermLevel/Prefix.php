<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

class Prefix extends Node
{
    protected string $_key = 'prefix';

    protected bool $_fieldKeyed = true;

    /**
     * Beginning characters of terms you wish to find in the provided <field>.
     *
     * @param string $value
     * @return static
     */
    public function value(string $value): static
    {
        return $this->addProperty('value', $value);
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
     * Allows ASCII case insensitive matching of the value with the indexed field values when set to true. Default is false which means the case sensitivity of matching depends on the underlying field’s mapping.
     *
     * @param bool $caseInsensitive
     * @return static
     */
    public function caseInsensitive(bool $caseInsensitive): static
    {
        return $this->addProperty('case_insensitive', $caseInsensitive);
    }
}

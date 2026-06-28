<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\Script;

/**
 * Returns documents that contain a minimum number of exact terms in a provided field.
 */
class TermsSet extends Node
{
    protected string $_key = 'terms_set';

    protected bool $_fieldKeyed = true;

    /**
     * Array of terms you wish to find in the provided <field>. To return a document, a required number of terms must exactly match the field values, including whitespace and capitalization.
     *
     * The required number of matching terms is defined in the minimum_should_match, minimum_should_match_field or minimum_should_match_script parameters. Exactly one of these parameters must be provided.
     *
     * @param array<int, string|int|float|bool> $value
     * @return static
     */
    public function terms(array $value): static
    {
        return $this->addProperty('terms', $value);
    }

    /**
     * Specification for the number of matching terms required to return a document.
     *
     * For valid values, see minimum_should_match parameter.
     *
     * @param int|string $value
     * @return static
     */
    public function minimumShouldMatch(int|string $value): static
    {
        return $this->addProperty('minimum_should_match', $value);
    }

    /**
     * @param string $value
     * @return static
     */
    public function minimumShouldMatchField(string $value): static
    {
        return $this->addProperty('minimum_should_match_field', $value);
    }

    /**
     * Custom script containing the number of matching terms required to return a document.
     *
     * For parameters and valid values, see Scripting.
     *
     * For an example query using the minimum_should_match_script parameter, see How to use the minimum_should_match_script parameter.
     *
     * @param mixed $value
     * @return static
     */
    public function minimumShouldMatchScript($value): static
    {
        return $this->addProperty('minimum_should_match_script', Script::create($value));
    }
}

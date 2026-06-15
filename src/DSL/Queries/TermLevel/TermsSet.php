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
     * @param array<int, string|int|float|bool> $terms
     * @return static
     */
    public function terms(array $terms): static
    {
        return $this->addProperty('terms', $terms);
    }

    /**
     * Specification for the number of matching terms required to return a document.
     *
     * For valid values, see minimum_should_match parameter.
     *
     * @param int|string $minimumShouldMatch
     * @return static
     */
    public function minimumShouldMatch(int|string $minimumShouldMatch): static
    {
        return $this->addProperty('minimum_should_match', $minimumShouldMatch);
    }

    /**
     * @param string $field
     * @return static
     */
    public function minimumShouldMatchField(string $field): static
    {
        return $this->addProperty('minimum_should_match_field', $field);
    }

    /**
     * Custom script containing the number of matching terms required to return a document.
     *
     * For parameters and valid values, see Scripting.
     *
     * For an example query using the minimum_should_match_script parameter, see How to use the minimum_should_match_script parameter.
     *
     * @param mixed $minimumShouldMatchScript
     * @return static
     */
    public function minimumShouldMatchScript($minimumShouldMatchScript): static
    {
        return $this->addProperty('minimum_should_match_script', Script::create($minimumShouldMatchScript));
    }
}

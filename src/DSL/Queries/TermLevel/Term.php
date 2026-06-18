<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

/**
 * Returns documents that contain an exact term in a provided field.
 *
 * You can use the term query to find documents based on a precise value such as a price, a product ID, or a username.
 *
 * Avoid using the term query for text fields.
 *
 * By default, Elasticsearch changes the values of text fields as part of analysis. This can make finding exact matches for text field values difficult.
 *
 * To search text field values, use the match query instead.
 */
class Term extends Node
{
    protected string $_key = 'term';

    protected bool $_fieldKeyed = true;

    /**
     * Term you wish to find in the provided <field>. To return a document, the term must exactly match the field value, including whitespace and capitalization.
     *
     * @param string|int|float|bool $value
     * @return static
     */
    public function value(string|int|float|bool $value): static
    {
        return $this->addProperty('value', $value);
    }

    /**
     *
     * Allows ASCII case insensitive matching of the value with the indexed field values when set to true. Default is false which means the case sensitivity of matching depends on the underlying field’s mapping.
     *
     * @param bool $value
     * @return static
     * @version 7.10.0
     */
    public function caseInsensitive(bool $value): static
    {
        return $this->addProperty('case_insensitive', $value);
    }
}

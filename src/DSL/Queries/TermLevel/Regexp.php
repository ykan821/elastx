<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

class Regexp extends Node
{
    protected string $_key = 'regexp';

    protected bool $_fieldKeyed = true;

    /**
     * Regular expression for terms you wish to find in the provided <field>. For a list of supported operators, see Regular expression syntax.
     *
     * By default, regular expressions are limited to 1,000 characters. You can change this limit using the index.max_regex_length setting.
     *
     * The performance of the regexp query can vary based on the regular expression provided. To improve performance, avoid using wildcard patterns, such as .* or .*?+, without a prefix or suffix.
     *
     * @param string $value
     * @return static
     */
    public function value(string $value): static
    {
        return $this->addProperty('value', $value);
    }

    /**
     * Enables optional operators for the regular expression. For valid values and more information, see Regular expression syntax.
     *
     * @param string $value
     * @return static
     */
    public function flags(string $value): static
    {
        return $this->addProperty('flags', $value);
    }

    /**
     * Allows case insensitive matching of the regular expression value with the indexed field values when set to true. Default is false which means the case sensitivity of matching depends on the underlying field’s mapping.
     *
     * @param bool $value
     * @return static
     */
    public function caseInsensitive(bool $value): static
    {
        return $this->addProperty('case_insensitive', $value);
    }

    /**
     * Maximum number of automaton states required for the query. Default is 10000.
     *
     * Elasticsearch uses Apache Lucene internally to parse regular expressions. Lucene converts each regular expression to a finite automaton containing a number of determinized states.
     *
     * You can use this parameter to prevent that conversion from unintentionally consuming too many resources. You may need to increase this limit to run complex regular expressions.
     *
     * @param int $value
     * @return static
     */
    public function maxDeterminizedStates(int $value): static
    {
        return $this->addProperty('max_determinized_states', $value);
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
}

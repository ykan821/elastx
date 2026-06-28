<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\TermLevel;

use ElasticKit\DSL\Node;

class Fuzzy extends Node
{
    protected string $_key = 'fuzzy';

    protected bool $_fieldKeyed = true;

    /**
     * Term you wish to find in the provided <field>.
     *
     * @param string $value
     * @return static
     */
    public function value(string $value): static
    {
        return $this->addProperty('value', $value);
    }

    /**
     * Maximum edit distance allowed for matching. See Fuzziness for valid values and more information.
     *
     * @param int|string $value
     * @return static
     */
    public function fuzziness(int|string $value): static
    {
        return $this->addProperty('fuzziness', $value);
    }

    /**
     * Maximum number of variations created. Defaults to 50.
     *
     * @param int $value
     * @return static
     */
    public function maxExpansions(int $value): static
    {
        return $this->addProperty('max_expansions', $value);
    }

    /**
     * Number of beginning characters left unchanged when creating expansions. Defaults to 0.
     *
     * @param int $value
     * @return static
     */
    public function prefixLength(int $value): static
    {
        return $this->addProperty('prefix_length', $value);
    }

    /**
     * Indicates whether edits include transpositions of two adjacent characters (ab → ba). Defaults to true.
     *
     * @param bool $value
     * @return static
     */
    public function transpositions(bool $value): static
    {
        return $this->addProperty('transpositions', $value);
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

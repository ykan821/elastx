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
     * @param int|string $fuzziness
     * @return static
     */
    public function fuzziness(int|string $fuzziness): static
    {
        return $this->addProperty('fuzziness', $fuzziness);
    }

    /**
     * Maximum number of variations created. Defaults to 50.
     *
     * @param int $maxExpansions
     * @return static
     */
    public function maxExpansions(int $maxExpansions): static
    {
        return $this->addProperty('max_expansions', $maxExpansions);
    }

    /**
     * Number of beginning characters left unchanged when creating expansions. Defaults to 0.
     *
     * @param int $prefixLength
     * @return static
     */
    public function prefixLength(int $prefixLength): static
    {
        return $this->addProperty('prefix_length', $prefixLength);
    }

    /**
     * Indicates whether edits include transpositions of two adjacent characters (ab → ba). Defaults to true.
     *
     * @param bool $transpositions
     * @return static
     */
    public function transpositions(bool $transpositions): static
    {
        return $this->addProperty('transpositions', $transpositions);
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
}

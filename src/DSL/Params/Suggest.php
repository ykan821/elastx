<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use ElasticKit\DSL\Node;

/**
 * Search suggestions based on term, completion, or phrase suggesters.
 *
 * @phpstan-consistent-constructor
 */
class Suggest extends Node
{
    protected string $_key = 'suggest';

    /**
     * Add a term suggester.
     *
     * @param string $alias
     * @param string $field
     * @param ?string $text
     * @return static
     */
    public function term(string $alias, string $field, ?string $text = null): static
    {
        $suggest = ['term' => ['field' => $field]];
        if ($text !== null) {
            $suggest['text'] = $text;
        }
        return $this->addProperty($alias, $suggest);
    }

    /**
     * Add a completion suggester.
     *
     * @param string $alias
     * @param string $field
     * @param ?string $prefix
     * @return static
     */
    public function completion(string $alias, string $field, ?string $prefix = null): static
    {
        $suggest = ['completion' => ['field' => $field]];
        if ($prefix !== null) {
            $suggest['prefix'] = $prefix;
        }
        return $this->addProperty($alias, $suggest);
    }

    /**
     * Add a phrase suggester.
     *
     * @param string $alias
     * @param string $field
     * @param ?string $text
     * @return static
     */
    public function phrase(string $alias, string $field, ?string $text = null): static
    {
        $suggest = ['phrase' => ['field' => $field]];
        if ($text !== null) {
            $suggest['text'] = $text;
        }
        return $this->addProperty($alias, $suggest);
    }
}

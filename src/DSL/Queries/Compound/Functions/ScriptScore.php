<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Compound\Functions;

use ElasticKit\DSL\Node;
use ElasticKit\DSL\Queries\Script;

/**
 * Wraps another query and customizes the scoring using a script expression.
 */
class ScriptScore extends Node
{
    protected string $_key = 'script_score';

    /**
     * The script used to compute the custom score.
     *
     * @param mixed $value
     * @return static
     */
    public function script($value): static
    {
        return $this->addProperty('script', Script::create($value));
    }
}

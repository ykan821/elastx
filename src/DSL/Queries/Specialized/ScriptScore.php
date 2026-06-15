<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Modifies the relevance score of documents using a custom script.
 */
class ScriptScore extends Node
{
    protected string $_key = 'script_score';

    /**
     * The base query whose scores will be modified by the script.
     *
     * @param mixed $query
     * @return static
     */
    public function query($query): static
    {
        return $this->addProperty('query', Query::create($query));
    }

    /**
     * The script used to compute the new relevance score.
     *
     * @param mixed $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', \ElasticKit\DSL\Queries\Script::create($script));
    }

    /**
     * Minimum relevance score threshold. Documents with a lower score are excluded.
     *
     * @param float $minScore
     * @return static
     */
    public function minScore(float $minScore): static
    {
        return $this->addProperty('min_score', Query::create($minScore));
    }
}

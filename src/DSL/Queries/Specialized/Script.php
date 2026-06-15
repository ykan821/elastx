<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries\Specialized;

use ElasticKit\DSL\Node;

/**
 * Filters documents using a custom script as a query.
 */
class Script extends Node
{
    protected string $_key = 'script';

    /**
     * The script to use as the query filter.
     *
     * @param mixed $script
     * @return static
     */
    public function script($script): static
    {
        return $this->addProperty('script', \ElasticKit\DSL\Queries\Script::create($script));
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Node;

/**
 * Represents an inline or stored script used in Elasticsearch queries and aggregations.
 */
class Script extends Node
{
    protected string $_key = 'script';

    /**
     * The ID of a stored script.
     *
     * @param string $value
     * @return static
     */
    public function id(string $value): static
    {
        return $this->addProperty('id', $value);
    }

    /**
     * The script language. Defaults to painless.
     *
     * @param string $value
     * @return static
     */
    public function lang(string $value): static
    {
        return $this->addProperty('lang', $value);
    }

    /**
     * The inline script source to execute.
     *
     * @param string $value
     * @return static
     */
    public function source(string $value): static
    {
        return $this->addProperty('source', $value);
    }

    /**
     * Named parameters passed into the script.
     *
     * @param array<string, mixed> $value
     * @return static
     */
    public function params(array $value): static
    {
        return $this->addProperty('params', $value);
    }
}

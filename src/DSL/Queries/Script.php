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
     * @param string $id
     * @return static
     */
    public function id(string $id): static
    {
        return $this->addProperty('id', $id);
    }

    /**
     * The script language. Defaults to painless.
     *
     * @param string $lang
     * @return static
     */
    public function lang(string $lang): static
    {
        return $this->addProperty('lang', $lang);
    }

    /**
     * The inline script source to execute.
     *
     * @param string $source
     * @return static
     */
    public function source(string $source): static
    {
        return $this->addProperty('source', $source);
    }

    /**
     * Named parameters passed into the script.
     *
     * @param array<string, mixed> $params
     * @return static
     */
    public function params(array $params): static
    {
        return $this->addProperty('params', $params);
    }
}

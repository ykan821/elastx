<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class IpPrefix extends Node
{
    protected string $_key = 'ip_prefix';

    /**
     * Length of the network prefix.
     *
     * @param int $value
     * @return static
     */
    public function prefixLength(int $value): static
    {
        return $this->addProperty('prefix_length', $value);
    }

    /**
     * @param int $value
     * @return static
     */
    public function minPrefixLength(int $value): static
    {
        return $this->addProperty('min_prefix_length', $value);
    }
}

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
     * @param int $length
     * @return static
     */
    public function prefixLength(int $length): static
    {
        return $this->addProperty('prefix_length', $length);
    }

    /**
     * @param int $length
     * @return static
     */
    public function minPrefixLength(int $length): static
    {
        return $this->addProperty('min_prefix_length', $length);
    }
}

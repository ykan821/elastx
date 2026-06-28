<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\MatchAll\MatchAll as QMatchAll;
use ElasticKit\DSL\Queries\MatchAll\MatchNone;

/**
 * Shortcut methods for match_all query types.
 */
trait MatchAll
{
    /**
     * Add a match_all query.
     *
     * @param mixed $value
     * @return $this
     */
    public function matchAll($value = null): static
    {
        return $this->addQuery(QMatchAll::create($value));
    }

    /**
     * Add a match_none query.
     *
     * @return $this
     */
    public function matchNone(): static
    {
        return $this->addQuery(MatchNone::create());
    }
}

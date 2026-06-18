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
     * @param mixed $matchAll
     * @return $this
     */
    public function matchAll($matchAll = null): static
    {
        return $this->addQuery(QMatchAll::create($matchAll));
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

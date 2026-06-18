<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\Specialized\DistanceFeature;
use ElasticKit\DSL\Queries\Specialized\MoreLikeThis;
use ElasticKit\DSL\Queries\Specialized\Percolate;
use ElasticKit\DSL\Queries\Specialized\Pinned;
use ElasticKit\DSL\Queries\Specialized\RankFeature;
use ElasticKit\DSL\Queries\Specialized\Script;
use ElasticKit\DSL\Queries\Specialized\ScriptScore;
use ElasticKit\DSL\Queries\Specialized\Wrapper;

/**
 * Shortcut methods for specialized query types.
 */
trait Specialized
{
    /**
     * Add a distance_feature query.
     *
     * @param mixed $value
     * @return $this
     */
    public function distanceFeature($value): static
    {
        return $this->addQuery(DistanceFeature::create($value));
    }

    /**
     * Add a more_like_this query.
     *
     * @param mixed $value
     * @return $this
     */
    public function moreLikeThis($value): static
    {
        return $this->addQuery(MoreLikeThis::create($value));
    }

    /**
     * Add a percolate query.
     *
     * @param mixed $value
     * @return $this
     */
    public function percolate($value): static
    {
        return $this->addQuery(Percolate::create($value));
    }

    /**
     * Add a rank_feature query.
     *
     * @param mixed $value
     * @return $this
     */
    public function rankFeature($value): static
    {
        return $this->addQuery(RankFeature::create($value));
    }

    /**
     * Add a script query.
     *
     * @param mixed $value
     * @return $this
     */
    public function script($value): static
    {
        return $this->addQuery(Script::create($value));
    }

    /**
     * Add a script_score query.
     *
     * @param mixed $value
     * @return $this
     */
    public function scriptScore($value): static
    {
        return $this->addQuery(ScriptScore::create($value));
    }

    /**
     * Add a wrapper query.
     *
     * @param mixed $value
     * @return $this
     */
    public function wrapper($value): static
    {
        return $this->addQuery(Wrapper::create($value));
    }

    /**
     * Add a pinned query.
     *
     * @param mixed $value
     * @return $this
     */
    public function pinned($value): static
    {
        return $this->addQuery(Pinned::create($value));
    }
}

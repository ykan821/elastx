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
     * @param mixed $distanceFeature
     * @return $this
     */
    public function distanceFeature($distanceFeature): static
    {
        return $this->addQuery(DistanceFeature::create($distanceFeature));
    }

    /**
     * Add a more_like_this query.
     *
     * @param mixed $moreLikeThis
     * @return $this
     */
    public function moreLikeThis($moreLikeThis): static
    {
        return $this->addQuery(MoreLikeThis::create($moreLikeThis));
    }

    /**
     * Add a percolate query.
     *
     * @param mixed $percolate
     * @return $this
     */
    public function percolate($percolate): static
    {
        return $this->addQuery(Percolate::create($percolate));
    }

    /**
     * Add a rank_feature query.
     *
     * @param mixed $rankFeature
     * @return $this
     */
    public function rankFeature($rankFeature): static
    {
        return $this->addQuery(RankFeature::create($rankFeature));
    }

    /**
     * Add a script query.
     *
     * @param mixed $script
     * @return $this
     */
    public function script($script): static
    {
        return $this->addQuery(Script::create($script));
    }

    /**
     * Add a script_score query.
     *
     * @param mixed $scriptScore
     * @return $this
     */
    public function scriptScore($scriptScore): static
    {
        return $this->addQuery(ScriptScore::create($scriptScore));
    }

    /**
     * Add a wrapper query.
     *
     * @param mixed $wrapper
     * @return $this
     */
    public function wrapper($wrapper): static
    {
        return $this->addQuery(Wrapper::create($wrapper));
    }

    /**
     * Add a pinned query.
     *
     * @param mixed $pinned
     * @return $this
     */
    public function pinned($pinned): static
    {
        return $this->addQuery(Pinned::create($pinned));
    }
}

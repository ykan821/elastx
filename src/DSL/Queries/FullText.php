<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Queries\FullText\CombinedFields;
use ElasticKit\DSL\Queries\FullText\Intervals;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\FullText\MatchBoolPrefix;
use ElasticKit\DSL\Queries\FullText\MatchPhrase;
use ElasticKit\DSL\Queries\FullText\MatchPhrasePrefix;
use ElasticKit\DSL\Queries\FullText\MultiMatch;
use ElasticKit\DSL\Queries\FullText\QueryString;
use ElasticKit\DSL\Queries\FullText\SimpleQueryString;

/**
 * Shortcut methods for full-text query types.
 */
trait FullText
{
    /**
     * Add an intervals query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function intervals($field, $value = null)
    {
        return $this->addQuery(Intervals::create($field, $value));
    }

    /**
     * Add a match query.
     *
     * @example $query->match('title', function (Match_ $m) { $m->query('test') })
     *
     * @param string|Match_ $field
     * @param callable|string|array<string, mixed> $value
     * @return $this
     */
    public function match($field, $value = null)
    {
        return $this->addQuery(Match_::create($field, $value));
    }

    /**
     * Add a match_phrase query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function matchPhrase($field, $value = null)
    {
        return $this->addQuery(MatchPhrase::create($field, $value));
    }

    /**
     * Add a match_phrase_prefix query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function matchPhrasePrefix($field, $value = null)
    {
        return $this->addQuery(MatchPhrasePrefix::create($field, $value));
    }

    /**
     * Add a match_bool_prefix query.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function matchBoolPrefix($field, $value = null)
    {
        return $this->addQuery(MatchBoolPrefix::create($field, $value));
    }

    /**
     * Add a multi_match query.
     *
     * @example $query->multiMatch(function (MultiMatch $m) { $m->query('test')->fields(['title', 'body']) })
     *
     * @param callable|MultiMatch|array<string, mixed> $value
     * @return $this
     */
    public function multiMatch($value)
    {
        return $this->addQuery(MultiMatch::create($value));
    }

    /**
     * Add a combined_fields query.
     *
     * @param mixed $value
     * @return $this
     */
    public function combinedFields($value)
    {
        return $this->addQuery(CombinedFields::create($value));
    }

    /**
     * Add a query_string query.
     *
     * @param mixed $queryString
     * @return $this
     */
    public function queryString($queryString)
    {
        return $this->addQuery(QueryString::create($queryString));
    }

    /**
     * Add a simple_query_string query.
     *
     * @param mixed $simpleQueryString
     * @return $this
     */
    public function simpleQueryString($simpleQueryString)
    {
        return $this->addQuery(SimpleQueryString::create($simpleQueryString));
    }
}

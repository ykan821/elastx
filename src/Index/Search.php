<?php

namespace ElasticKit\Index;

use BadMethodCallException;
use ElasticKit\DSL\Query;
use RuntimeException;
use stdClass;

/**
 * Executable search that composes Query for DSL building.
 *
 * @mixin Query
 */
class Search
{
    use StatsSupport;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var array<string, mixed>
     */
    private $urlParams = [];

    /**
     * @param Index $index
     */
    public function __construct(Index $index, Query $query = null)
    {
        $this->query = $query ?? new Query();
        $this->index = $index;
    }

    /**
     * Custom routing value to target specific shards.
     *
     * @param string $routing
     * @return $this
     */
    public function routing($routing)
    {
        $this->urlParams['routing'] = $routing;
        return $this;
    }

    /**
     * Delegate DSL method calls to the internal Query instance.
     *
     * @param string $method
     * @param array<int, mixed> $args
     * @return $this|mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->query, $method)) {
            throw new BadMethodCallException(
                sprintf('Method %s does not exist on %s', $method, get_class($this->query))
            );
        }

        $result = $this->query->$method(...$args);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }

    /**
     * Execute the search and return a Results wrapper.
     *
     * @return Results
     */
    public function get()
    {
        return new Results($this->doSearch('get'));
    }

    /**
     * Execute the search and return the first document _source.
     *
     * @return array<string, mixed>|null
     */
    public function first()
    {
        $saved = $this->query;
        $this->query = clone $this->query;
        $this->query->size(1);
        try {
            $response = $this->doSearch('first');
        } finally {
            $this->query = $saved;
        }

        $docs = (new Results($response))->docs();
        return $docs[0] ?? null;
    }

    /**
     * Return the total number of matching documents.
     *
     * @return int
     */
    public function count()
    {
        $response = $this->doCount();

        if (!isset($response['count'])) {
            throw new RuntimeException('Missing "count" in Elasticsearch response.');
        }

        return $response['count'];
    }

    /**
     * Execute or continue a scroll search. Defaults to 1000 documents per batch if size is not set.
     *
     * @param string|null $scrollId continue an existing scroll, or start a new one if null
     * @param string $duration
     * @return Results
     */
    public function scroll($scrollId = null, $duration = '5m')
    {
        if ($scrollId !== null) {
            return $this->doScroll($scrollId, $duration);
        }

        $saved = $this->query;
        $this->query = clone $this->query;

        if (!$this->query->hasParam('size')) {
            $this->query->size(1000);
        }

        try {
            $response = $this->doSearch('scroll', ['scroll' => $duration]);
        } finally {
            $this->query = $saved;
        }

        return new Results($response);
    }

    /**
     * Fetch the next batch of scroll results.
     *
     * @param Results $results
     * @param string $duration
     * @return Results
     */
    public function next(Results $results, $duration = '5m')
    {
        return $this->doScroll($results->scrollId(), $duration);
    }

    /**
     * Clear the scroll context on the server.
     *
     * @param Results $results
     * @return void
     */
    public function clear(Results $results)
    {
        $scrollId = $results->scrollId();
        if ($scrollId !== null) {
            $this->index->getClient()->clearScroll([
                'scroll_id' => $scrollId,
            ]);
        }
    }

    /**
     * Execute a scroll request with before/after events.
     *
     * @param string $scrollId
     * @param string $duration
     * @return Results
     */
    protected function doScroll($scrollId, $duration)
    {
        $indexName = $this->index->name();

        $e = new Event('search.scroll.before', $indexName);
        $e->action = 'scroll';
        $e->scrollId = $scrollId;
        EventDispatcher::dispatch($e);

        $start = microtime(true);
        $response = $this->index->getClient()->scroll([
            'scroll_id' => $scrollId,
            'scroll' => $duration,
        ]);
        $response = $response->asArray();
        $durationTime = microtime(true) - $start;

        $e = new Event('search.scroll.after', $indexName);
        $e->action = 'scroll';
        $e->scrollId = $scrollId;
        $e->response = $response;
        $e->duration = $durationTime;
        EventDispatcher::dispatch($e);

        return new Results($response);
    }

    /**
     * Return a generator that yields Results batches via scroll.
     *
     * @param string $duration
     * @return \Generator
     */
    public function cursor($duration = '5m')
    {
        $results = $this->scroll(null, $duration);

        try {
            while ($results->hasMore()) {
                yield $results;
                $results = $this->next($results, $duration);
            }
        } finally {
            $this->clear($results);
        }
    }

    /**
     * Execute a paginated search. Uses pageResolver if no arguments are given.
     *
     * @param int|null $page
     * @param int|null $perPage
     * @return Results
     */
    public function paginate($page = null, $perPage = null)
    {
        if ($page === null && $perPage === null) {
            $resolver = Pagination::getPageResolver();
            if ($resolver !== null) {
                [$page, $perPage] = $resolver();
            }
        }

        $page = $page ?? 1;
        $perPage = $perPage ?? $this->index->perPage();

        $maxPerPage = $this->index->maxPerPage();
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        $saved = $this->query;
        $this->query = clone $this->query;
        $this->query->from(($page - 1) * $perPage);
        $this->query->size($perPage);
        try {
            $response = $this->doSearch('paginate');
        } finally {
            $this->query = $saved;
        }

        return (new Results($response))->paginate($page, $perPage);
    }

    /**
     * Execute an ES count call with before/after events.
     *
     * @return array<string, mixed>
     */
    protected function doCount()
    {
        $indexName = $this->index->name();
        $body = $this->query->toArray() ?: new stdClass();

        $e = new Event('search.query.before', $indexName);
        $e->dsl = $body;
        $e->action = 'count';
        EventDispatcher::dispatch($e);

        $start = microtime(true);
        $response = $this->index->getClient()->count([
            'index' => $indexName,
            'body' => $body,
        ]);
        $response = $response->asArray();
        $duration = microtime(true) - $start;

        $e = new Event('search.query.after', $indexName);
        $e->dsl = $body;
        $e->response = $response;
        $e->duration = $duration;
        $e->action = 'count';
        EventDispatcher::dispatch($e);

        return $response;
    }

    /**
     * Execute an ES search call with before/after events.
     *
     * @param string $action calling method name (get, first, scroll, paginate)
     * @param array<string, mixed> $extra extra request params (e.g. scroll)
     * @return array<string, mixed>
     */
    protected function doSearch($action, array $extra = [])
    {
        $indexName = $this->index->name();
        $body = $this->query->toArray() ?: new stdClass();

        $e = new Event('search.query.before', $indexName);
        $e->dsl = $body;
        $e->action = $action;
        EventDispatcher::dispatch($e);

        $params = array_merge(['index' => $indexName, 'body' => $body], $this->urlParams, $extra);

        $start = microtime(true);
        $response = $this->index->getClient()->search($params);
        $response = $response->asArray();
        $duration = microtime(true) - $start;

        $e = new Event('search.query.after', $indexName);
        $e->dsl = $body;
        $e->response = $response;
        $e->duration = $duration;
        $e->action = $action;
        EventDispatcher::dispatch($e);

        return $response;
    }
}

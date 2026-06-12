<?php

namespace ElasticKit\Index;

use RuntimeException;

/**
 * Lightweight wrapper for Elasticsearch search response.
 */
class Results
{
    /**
     * @var array<string, mixed>
     */
    protected $response;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var bool
     */
    protected $paginated = false;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Set pagination metadata on this Results instance.
     *
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function paginate($page, $perPage)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->paginated = true;
        return $this;
    }

    /**
     * Return the total number of matching documents.
     *
     * @return int
     */
    public function total()
    {
        return $this->response['hits']['total']['value'] ?? 0;
    }

    /**
     * Return the raw hits array (with _source, _id, _score, etc).
     *
     * @return array<int, array<string, mixed>>
     */
    public function hits()
    {
        return $this->response['hits']['hits'] ?? [];
    }

    /**
     * Return an array of _source values from all hits.
     *
     * @return array<int, array<string, mixed>|null>
     */
    public function docs()
    {
        return array_column($this->hits(), '_source');
    }

    /**
     * Return an array of document _id values from all hits.
     *
     * @return array<int, string>
     */
    public function ids()
    {
        return array_column($this->hits(), '_id');
    }

    /**
     * Return the first document _source, or null if no hits.
     *
     * @return array<string, mixed>|null
     */
    public function first()
    {
        $docs = $this->docs();
        return $docs[0] ?? null;
    }

    /**
     * Return the aggregations from the response, or null if none were requested.
     *
     * @return array<string, mixed>|null
     */
    public function aggregations()
    {
        return $this->response['aggregations'] ?? null;
    }

    /**
     * Return the scroll ID from the response.
     *
     * @return string|null
     */
    public function scrollId()
    {
        return $this->response['_scroll_id'] ?? null;
    }

    /**
     * Return the hits.total.relation value from the Elasticsearch response.
     *
     * "eq" = total is exact, "gte" = total is a lower bound.
     *
     * @return string "eq" or "gte"
     */
    public function totalRelation()
    {
        return $this->response['hits']['total']['relation'];
    }

    /**
     * Return whether the current batch contains hits.
     *
     * @return bool
     */
    public function hasMore()
    {
        return !empty($this->response['hits']['hits']);
    }

    /**
     * Return the time in milliseconds it took Elasticsearch to process the request.
     *
     * @return int
     */
    public function took()
    {
        return $this->response['took'] ?? 0;
    }

    /**
     * Return whether the request timed out before completing.
     *
     * @return bool
     */
    public function timedOut()
    {
        return $this->response['timed_out'] ?? false;
    }

    /**
     * Return the raw Elasticsearch response array.
     *
     * @return array<string, mixed>
     */
    public function raw()
    {
        return $this->response;
    }

    /**
     * Return the current page number.
     *
     * @return int
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * Return the number of results per page.
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Return the last page number.
     *
     * @return int
     */
    public function lastPage()
    {
        return (int) ceil($this->total() / $this->perPage) ?: 1;
    }

    /**
     * Alias for docs(), aligned with paginator semantics.
     *
     * @return array<int, array<string, mixed>|null>
     */
    public function items()
    {
        return $this->docs();
    }

    /**
     * Return whether the result set is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->response['hits']['hits']);
    }

    /**
     * Convert to a framework paginator using the registered resolver.
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function toPaginator()
    {
        if (!$this->paginated) {
            throw new RuntimeException(
                'Cannot create paginator from non-paginated results. Call paginate() first.'
            );
        }

        $resolver = Pagination::getPaginatorResolver();
        if ($resolver === null) {
            throw new RuntimeException(
                'Paginator resolver not registered. Call Index::setPaginatorResolver() first.'
            );
        }

        return $resolver($this, $this->page, $this->perPage);
    }
}

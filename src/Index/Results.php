<?php

declare(strict_types=1);

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
    protected array $response;

    /**
     * @var int
     */
    protected int $page = 1;

    /**
     * @var int
     */
    protected int $perPage = 15;

    /**
     * @var bool
     */
    protected bool $paginated = false;

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
    public function paginate(int $page, int $perPage): static
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
    public function total(): int
    {
        return $this->response['hits']['total']['value'] ?? 0;
    }

    /**
     * Return the raw hits array (with _source, _id, _score, etc).
     *
     * @return array<int, array<string, mixed>>
     */
    public function hits(): array
    {
        return $this->response['hits']['hits'] ?? [];
    }

    /**
     * Return an array of _source values from all hits.
     *
     * @return array<int, array<string, mixed>|null>
     */
    public function docs(): array
    {
        return array_column($this->hits(), '_source');
    }

    /**
     * Return an array of document _id values from all hits.
     *
     * @return array<int, string>
     */
    public function ids(): array
    {
        return array_column($this->hits(), '_id');
    }

    /**
     * Return the first document _source, or null if no hits.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $docs = $this->docs();
        return $docs[0] ?? null;
    }

    /**
     * Return the aggregations from the response, or null if none were requested.
     *
     * @return array<string, mixed>|null
     */
    public function aggregations(): ?array
    {
        return $this->response['aggregations'] ?? null;
    }

    /**
     * Return the scroll ID from the response.
     *
     * @return string|null
     */
    public function scrollId(): ?string
    {
        return $this->response['_scroll_id'] ?? null;
    }

    /**
     * Return the hits.total.relation value from the Elasticsearch response.
     *
     * "eq" = total is exact, "gte" = total is a lower bound.
     *
     * @return string|null "eq" or "gte"
     */
    public function totalRelation(): ?string
    {
        return $this->response['hits']['total']['relation'] ?? null;
    }

    /**
     * Return whether the current batch contains hits.
     *
     * @return bool
     */
    public function hasMore(): bool
    {
        return !empty($this->response['hits']['hits']);
    }

    /**
     * Return the time in milliseconds it took Elasticsearch to process the request.
     *
     * @return int
     */
    public function took(): int
    {
        return $this->response['took'] ?? 0;
    }

    /**
     * Return whether the request timed out before completing.
     *
     * @return bool
     */
    public function timedOut(): bool
    {
        return $this->response['timed_out'] ?? false;
    }

    /**
     * Return the raw Elasticsearch response array.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->response;
    }

    /**
     * Return the current page number.
     *
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * Return the number of results per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Return the last page number.
     *
     * @return int
     */
    public function lastPage(): int
    {
        return (int) ceil($this->total() / $this->perPage) ?: 1;
    }

    /**
     * Alias for docs(), aligned with paginator semantics.
     *
     * @return array<int, array<string, mixed>|null>
     */
    public function items(): array
    {
        return $this->docs();
    }

    /**
     * Return whether the result set is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
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

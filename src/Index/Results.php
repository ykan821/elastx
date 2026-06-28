<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use ElasticKit\Index\Support\Pagination;
use RuntimeException;
use ElasticKit\Index\Exception\PaginationTotalUnavailableException;

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
     * Return the total number of matching documents, or null when unavailable.
     *
     * Null when track_total_hits is false (Elasticsearch omits hits.total).
     *
     * @return int|null
     */
    public function total(): ?int
    {
        return $this->response['hits']['total']['value'] ?? null;
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
     * Whether the current result set has no hits.
     *
     * For scroll loops: `while (! $results->isEmpty())`. For pagination
     * "has a next page", use hasMorePages() (works with or without a total).
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->response['hits']['hits']);
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
     * Return the last page number, or null when the total is unavailable.
     *
     * @return int|null
     */
    public function lastPage(): ?int
    {
        if ($this->total() === null) {
            return null;
        }

        if ($this->perPage < 1) {
            return 1;
        }

        return (int) ceil($this->total() / $this->perPage) ?: 1;
    }

    /**
     * Whether there is a page after the current one.
     *
     * With a known total: page() < lastPage(). Without one (track_total_hits
     * is false): a full page implies more, a partial page is the last.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        $lastPage = $this->lastPage();

        if ($lastPage !== null) {
            return $this->page < $lastPage;
        }

        return $this->perPage > 0 && count($this->hits()) === $this->perPage;
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

        if ($this->total() === null) {
            throw new PaginationTotalUnavailableException(
                'Cannot build a length-aware paginator: total is unavailable (track_total_hits is false). '
                . 'Enable track_total_hits on the index, or use hasMorePages()/chunk() for total-less pagination.'
            );
        }

        $resolver = Pagination::getPaginatorResolver();
        if ($resolver === null) {
            throw new RuntimeException(
                'Paginator resolver not registered. Call Pagination::setPaginatorResolver() first.'
            );
        }

        return $resolver($this, $this->page, $this->perPage);
    }
}

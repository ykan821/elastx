<?php

declare(strict_types=1);

namespace ElasticKit\Index\Exception;

use RuntimeException;

/**
 * Thrown when a length-aware paginator is requested without a hit total.
 *
 * Elasticsearch omits hits.total when track_total_hits is false, so
 * Results::total()/lastPage() are null and a length-aware paginator cannot
 * be built. Enable track_total_hits on the index, or use total-less
 * pagination (Results::hasMorePages() / Search::chunk()).
 */
class PaginationTotalUnavailableException extends RuntimeException
{
}

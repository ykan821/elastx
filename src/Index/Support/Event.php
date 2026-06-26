<?php

declare(strict_types=1);

namespace ElasticKit\Index\Support;

/**
 * Lightweight event object carrying event name, index, and contextual data.
 *
 * Properties are real typed properties: typos throw an Error (not silent null),
 * and IDEs/phpstan can validate them. Reading an unset property returns null.
 */
class Event
{
    public string $name;

    public string $index;

    /** @var array<string, mixed>|\stdClass|null Request body (search.query.before/after) */
    public array|\stdClass|null $dsl = null;

    /** @var string|null Calling method: get/first/count/scroll/paginate (search events) */
    public ?string $action = null;

    /** @var array<string, mixed>|null ES API response (all after events) */
    public ?array $response = null;

    /** @var float|null Execution time in seconds (all after events) */
    public ?float $duration = null;

    /** @var string|null Scroll context ID (search.scroll events) */
    public ?string $scrollId = null;

    /** @var array<int, mixed>|null Bulk action lines (bulk.flush events) */
    public ?array $actions = null;

    /** @var string|null New backing index name (rebuild.run.after) */
    public ?string $newIndex = null;

    /** @var string|null Previous backing index name (rebuild.run.after) */
    public ?string $oldIndex = null;

    public function __construct(string $name, string $index)
    {
        $this->name = $name;
        $this->index = $index;
    }
}

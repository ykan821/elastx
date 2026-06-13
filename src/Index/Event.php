<?php

declare(strict_types=1);

namespace ElasticKit\Index;

/**
 * Lightweight event object carrying event name, index, and contextual data.
 *
 * @property string $name Event name (e.g. 'search.query.after')
 * @property string $index Index name
 * @property array<string, mixed>|null $dsl Request body (search.query.before/after)
 * @property string|null $action Calling method name: get, first, count, scroll, paginate (search.query/scroll events)
 * @property array<string, mixed>|null $response ES API response (all after events)
 * @property float|null $duration Execution time in seconds (all after events)
 * @property string|null $scrollId Scroll context ID (search.scroll events)
 * @property array<int, mixed>|null $actions Bulk action lines (bulk.execute events)
 * @property string|null $newIndex New backing index name (rebuild.run.after)
 * @property string|null $oldIndex Previous backing index name (rebuild.run.after)
 */
class Event
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $index;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param string $name
     * @param string $index
     */
    public function __construct(string $name, string $index)
    {
        $this->name = $name;
        $this->index = $index;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }
}

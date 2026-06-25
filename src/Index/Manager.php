<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use RuntimeException;
use stdClass;

/**
 * Index management operations (create, delete, mappings, settings, aliases).
 */
class Manager
{
    public function __construct(
        private readonly Index $index
    ) {
    }

    /**
     * Create the index using mappings and settings defined on the Index subclass.
     *
     * Note: this creates a real index (not an alias). Rebuild::run() requires an alias
     * to swap atomically, so it will reject a real index. Use Rebuild to initialize
     * from scratch if you plan to use zero-downtime rebuilds later.
     *
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $indexName = $this->index->name();

        $e = new Event('manager.create.before', $indexName);
        EventDispatcher::dispatch($e);

        $mappings = $this->index->mappings();
        $settings = $this->index->settings();

        $response = $this->index->getClient()->indices()->create([
            'index' => $indexName,
            'body' => [
                'mappings' => empty($mappings) ? new stdClass() : $mappings,
                'settings' => empty($settings) ? new stdClass() : $settings,
            ],
        ])->asArray();

        $e = new Event('manager.create.after', $indexName);
        $e->response = $response;
        EventDispatcher::dispatch($e);

        return $response;
    }

    /**
     * Delete the index.
     *
     * Refuses by default when name() is an alias: deleting a backing index is
     * destructive and irreversible. Pass resolveAlias=true to delete the backing
     * index(es) the alias points to; use removeAlias() to drop the alias itself.
     *
     * @param bool $resolveAlias delete the backing index(es) when name() is an alias
     * @return array<string, mixed>
     * @throws RuntimeException when name() is an alias and resolveAlias is false
     */
    public function delete(bool $resolveAlias = false): array
    {
        $name = $this->index->name();
        $indices = $this->index->getClient()->indices();

        $isAlias = $indices->existsAlias(['name' => $name])->asBool();

        if ($isAlias && !$resolveAlias) {
            throw new RuntimeException(sprintf(
                'Index [%s] is an alias; pass resolveAlias=true to delete its backing index(es), or removeAlias() to drop the alias.',
                $name
            ));
        }

        $target = $name;
        if ($isAlias) {
            $aliases = $indices->getAlias(['name' => $name])->asArray();
            $target = implode(',', array_keys($aliases));
        }

        $e = new Event('manager.delete.before', $target);
        EventDispatcher::dispatch($e);

        $response = $indices->delete(['index' => $target])->asArray();

        $e = new Event('manager.delete.after', $target);
        $e->response = $response;
        EventDispatcher::dispatch($e);

        return $response;
    }

    /**
     * Check if the index exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->index->getClient()->indices()->exists([
            'index' => $this->index->name(),
        ])->asBool();
    }

    /**
     * Get index information (mappings, settings, aliases).
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->index->getClient()->indices()->get([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Update the index mapping using mappings() defined on the Index subclass.
     *
     * @return array<string, mixed>
     */
    public function putMapping(): array
    {
        $mappings = $this->index->mappings();

        return $this->index->getClient()->indices()->putMapping([
            'index' => $this->index->name(),
            'body' => empty($mappings) ? new stdClass() : $mappings,
        ])->asArray();
    }

    /**
     * Get the index mapping.
     *
     * @return array<string, mixed>
     */
    public function getMapping(): array
    {
        return $this->index->getClient()->indices()->getMapping([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Update index settings (only dynamic settings on open indices).
     *
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function putSettings(array $settings): array
    {
        return $this->index->getClient()->indices()->putSettings([
            'index' => $this->index->name(),
            'body' => $settings,
        ])->asArray();
    }

    /**
     * Get index settings.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->index->getClient()->indices()->getSettings([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Refresh the index to make recent operations searchable.
     *
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        return $this->index->getClient()->indices()->refresh([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Force merge the index segments.
     *
     * @param array<string, mixed> $options ES params: max_num_segments, only_expunge_deletes, flush.
     * @return array<string, mixed>
     */
    public function forceMerge(array $options = []): array
    {
        return $this->index->getClient()->indices()->forcemerge(
            array_merge(['index' => $this->index->name()], $options)
        )->asArray();
    }

    /**
     * Close the index (blocks read/write, reduces resource usage).
     *
     * @return array<string, mixed>
     */
    public function close(): array
    {
        return $this->index->getClient()->indices()->close([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Open a closed index.
     *
     * @return array<string, mixed>
     */
    public function open(): array
    {
        return $this->index->getClient()->indices()->open([
            'index' => $this->index->name(),
        ])->asArray();
    }

    /**
     * Add an alias to this index.
     * If the index name is itself an alias, resolves to the backing index first.
     *
     * @param string $alias
     * @param array<string, mixed> $options additional alias options: routing, filter, is_write_index.
     * @return array<string, mixed>
     */
    public function addAlias(string $alias, array $options = []): array
    {
        $indexName = $this->resolveIndexName();

        $params = [
            'index' => $indexName,
            'name' => $alias,
        ];

        if (!empty($options)) {
            $params['body'] = $options;
        }

        return $this->index->getClient()->indices()->putAlias($params)->asArray();
    }

    /**
     * Remove an alias from this index.
     * If the index name is itself an alias, resolves to the backing index first.
     *
     * @param string $alias
     * @return array<string, mixed>
     */
    public function removeAlias(string $alias): array
    {
        $indexName = $this->resolveIndexName();

        return $this->index->getClient()->indices()->deleteAlias([
            'index' => $indexName,
            'name' => $alias,
        ])->asArray();
    }

    /**
     * Atomically swap an alias from another index to this index.
     *
     * @param string $alias
     * @param string $fromIndex
     * @return array<string, mixed>
     */
    public function swapAlias(string $alias, string $fromIndex): array
    {
        $indexName = $this->index->name();

        $e = new Event('manager.swap_alias.before', $indexName);
        EventDispatcher::dispatch($e);

        $response = $this->index->getClient()->indices()->updateAliases([
            'body' => [
                'actions' => [
                    ['remove' => ['index' => $fromIndex, 'alias' => $alias]],
                    ['add' => ['index' => $indexName, 'alias' => $alias]],
                ],
            ],
        ])->asArray();

        $e = new Event('manager.swap_alias.after', $indexName);
        $e->response = $response;
        EventDispatcher::dispatch($e);

        return $response;
    }

    /**
     * Get aliases for this index.
     * If the index name is itself an alias, resolves to the backing index first.
     *
     * @return array<string, mixed>
     */
    public function getAliases(): array
    {
        $indexName = $this->resolveIndexName();

        return $this->index->getClient()->indices()->getAlias([
            'index' => $indexName,
        ])->asArray();
    }

    /**
     * Resolve the real index name: if name() is an alias, return the backing index.
     *
     * @return string
     */
    private function resolveIndexName(): string
    {
        $name = $this->index->name();
        $client = $this->index->getClient()->indices();

        if ($client->existsAlias(['name' => $name])->asBool()) {
            $aliases = $client->getAlias(['name' => $name])->asArray();
            return array_key_first($aliases) ?? $name;
        }

        return $name;
    }
}

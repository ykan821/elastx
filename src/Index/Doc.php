<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use RuntimeException;

/**
 * Lightweight reference to a single document for CRUD operations.
 *
 * $id may be null/empty for index()/create() — Elasticsearch auto-generates an
 * id. get()/source()/exists()/update()/delete() require an explicit id.
 */
class Doc
{
    /**
     * @var int
     */
    private int $retryOnConflict = 0;

    /**
     * @var string|null
     */
    private ?string $refresh = null;

    /**
     * @param string|int|null $id document id, or null/'' to let ES auto-generate
     */
    public function __construct(
        private readonly Index $index,
        private readonly string|int|null $id
    ) {
    }

    /**
     * Return the document ID.
     *
     * @return string|int|null
     */
    public function id(): string|int|null
    {
        return $this->id;
    }

    /**
     * Set retry_on_conflict for the next write operation.
     *
     * @param int $count
     * @return $this
     */
    public function retryOnConflict(int $count): static
    {
        $this->retryOnConflict = $count;

        return $this;
    }

    /**
     * Set refresh for the next write operation (true/false/wait_for).
     *
     * @param string $value
     * @return $this
     */
    public function refresh(string $value): static
    {
        $this->refresh = $value;

        return $this;
    }

    /**
     * Get the full document (with _source, _id, _version, etc).
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $id = $this->requireId('get');

        return $this->index->getClient()->get([
            'index' => $this->index->name(),
            'id' => $id,
        ])->asArray();
    }

    /**
     * Get the document _source only.
     *
     * @return array<string, mixed>
     */
    public function source(): array
    {
        $id = $this->requireId('source');

        return $this->index->getClient()->getSource([
            'index' => $this->index->name(),
            'id' => $id,
        ])->asArray();
    }

    /**
     * Check if the document exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $id = $this->requireId('exists');

        return $this->index->getClient()->exists([
            'index' => $this->index->name(),
            'id' => $id,
        ])->asBool();
    }

    /**
     * Update the document (partial update with optional upsert).
     * Chain retryOnConflict() and refresh() for common options.
     * For other ES options (routing, detect_noop, etc), use getClient()->update() directly.
     *
     * @param array<string, mixed> $data
     * @param bool $upsert
     * @return array<string, mixed>
     */
    public function update(array $data, bool $upsert = false): array
    {
        $id = $this->requireId('update');

        $params = [
            'index' => $this->index->name(),
            'id' => $id,
            'body' => [
                'doc' => $data,
                'doc_as_upsert' => $upsert,
            ],
        ];

        if ($this->retryOnConflict > 0) {
            $params['retry_on_conflict'] = $this->retryOnConflict;
        }

        if ($this->refresh !== null) {
            $params['refresh'] = $this->refresh;
        }

        $this->resetOptions();

        return $this->index->getClient()->update($params)->asArray();
    }

    /**
     * Index (create or overwrite) the document.
     *
     * If $id is null or empty, ES auto-generates an id.
     *
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    public function index(array $document): array
    {
        $params = [
            'index' => $this->index->name(),
        ];

        if ($this->id !== null && $this->id !== '') {
            $params['id'] = $this->id;
        }

        $params['body'] = $document;

        if ($this->refresh !== null) {
            $params['refresh'] = $this->refresh;
        }

        $this->resetOptions();

        return $this->index->getClient()->index($params)->asArray();
    }

    /**
     * Alias for index(). Create or overwrite the document.
     *
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    public function save(array $document): array
    {
        return $this->index($document);
    }

    /**
     * Create the document (fail if already exists).
     *
     * If $id is null or empty, ES auto-generates an id (always a create, since
     * auto-generated ids are unique).
     *
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    public function create(array $document): array
    {
        $params = [
            'index' => $this->index->name(),
        ];

        if ($this->id !== null && $this->id !== '') {
            $params['id'] = $this->id;
        }

        $params['body'] = $document;
        $params['op_type'] = 'create';

        if ($this->refresh !== null) {
            $params['refresh'] = $this->refresh;
        }

        $this->resetOptions();

        return $this->index->getClient()->index($params)->asArray();
    }

    /**
     * Delete the document.
     *
     * @return array<string, mixed>
     */
    public function delete(): array
    {
        $id = $this->requireId('delete');

        $params = [
            'index' => $this->index->name(),
            'id' => $id,
        ];

        if ($this->refresh !== null) {
            $params['refresh'] = $this->refresh;
        }

        $this->resetOptions();

        return $this->index->getClient()->delete($params)->asArray();
    }

    /**
     * Resolve the document id, throwing for operations that cannot auto-generate.
     *
     * get/source/exists/update/delete address an existing document and require
     * an explicit id; only index/create let ES auto-generate.
     *
     * @return string|int
     * @throws RuntimeException if the id is null or empty
     */
    private function requireId(string $operation): string|int
    {
        if ($this->id === null || $this->id === '') {
            throw new RuntimeException(sprintf(
                '%s() requires an explicit document id; got null/empty. '
                . 'Use index() or create() to let Elasticsearch auto-generate one.',
                $operation
            ));
        }

        return $this->id;
    }

    /**
     * Reset pending options after a write operation.
     */
    private function resetOptions(): void
    {
        $this->retryOnConflict = 0;
        $this->refresh = null;
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use InvalidArgumentException;
use RuntimeException;

/**
 * Batch document operations using the ES _bulk API.
 */
class Bulk
{
    /**
     * @var array<int, mixed>
     */
    private array $body = [];

    /**
     * @var int
     */
    private int $retryOnConflict = 0;

    /**
     * @var string|null
     */
    private ?string $targetIndex = null;

    /**
     * @var int
     */
    private int $batchSize = 0;

    /**
     * @var int
     */
    private int $docCount = 0;

    /**
     * @var callable|null
     */
    private $errorHandler = null;

    public function __construct(
        private readonly Index $index
    ) {
    }

    /**
     * Override the target index name for all actions.
     *
     * @param string $indexName
     * @return $this
     * @throws \InvalidArgumentException if indexName starts with a dot (system index)
     */
    public function target(string $indexName): static
    {
        if (strpos($indexName, '.') === 0) {
            throw new InvalidArgumentException("System index names (starting with '.') are not allowed: {$indexName}");
        }

        $this->targetIndex = $indexName;

        return $this;
    }

    /**
     * Auto-execute when doc count reaches this batch size.
     *
     * @param int $size
     * @return $this
     */
    public function batchSize(int $size): static
    {
        $this->batchSize = $size;

        return $this;
    }

    /**
     * Set a callback to handle bulk errors.
     *
     * The callback receives the raw ES response. To continue execution, simply
     * return without throwing. To abort, throw an exception from the callback.
     * Without an error handler, execute() throws RuntimeException on errors.
     *
     * @param callable $handler function (array $response): void
     * @return $this
     */
    public function onError(callable $handler): static
    {
        $this->errorHandler = $handler;
        return $this;
    }

    /**
     * Set retry_on_conflict for all update actions in this batch.
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
     * Queue an index (create/overwrite) action.
     *
     * @param string|int|null $id document ID, or null to let ES auto-generate
     * @param array<string, mixed> $document
     * @return $this
     */
    public function index(string|int|null $id, array $document): static
    {
        $action = ['index' => ['_index' => $this->resolveIndex()]];
        if ($id !== null && $id !== '') {
            $action['index']['_id'] = $id;
        }
        $this->body[] = $action;
        $this->body[] = $document;
        $this->afterPush();

        return $this;
    }

    /**
     * Alias for index(). Queue a save (create/overwrite) action.
     *
     * @param string|int|null $id
     * @param array<string, mixed> $document
     * @return $this
     */
    public function save(string|int|null $id, array $document): static
    {
        return $this->index($id, $document);
    }

    /**
     * Queue a create action (fail if document already exists).
     *
     * @param string|int $id
     * @param array<string, mixed> $document
     * @return $this
     */
    public function create(string|int $id, array $document): static
    {
        $this->body[] = ['create' => ['_index' => $this->resolveIndex(), '_id' => $id]];
        $this->body[] = $document;
        $this->afterPush();

        return $this;
    }

    /**
     * Queue an update (partial) action.
     * Chain retryOnConflict() for version conflict retry.
     * For other ES options (routing, detect_noop, etc), use getClient()->bulk() directly.
     *
     * @param string|int $id
     * @param array<string, mixed> $data
     * @param bool $upsert
     * @return $this
     */
    public function update(string|int $id, array $data, bool $upsert = false): static
    {
        $action = ['update' => ['_index' => $this->resolveIndex(), '_id' => $id]];

        if ($this->retryOnConflict > 0) {
            $action['update']['retry_on_conflict'] = $this->retryOnConflict;
        }

        $this->body[] = $action;
        $this->body[] = ['doc' => $data, 'doc_as_upsert' => $upsert];
        $this->afterPush();

        return $this;
    }

    /**
     * Queue a delete action.
     *
     * @param string|int $id
     * @return $this
     */
    public function delete(string|int $id): static
    {
        $this->body[] = ['delete' => ['_index' => $this->resolveIndex(), '_id' => $id]];
        $this->afterPush();

        return $this;
    }

    /**
     * Execute all queued actions and return the raw ES response.
     *
     * @param array<string, mixed> $options top-level bulk API params (refresh, timeout, etc)
     * @return array<string, mixed>
     */
    public function execute(array $options = []): array
    {
        if (empty($this->body)) {
            return [];
        }

        $indexName = $this->resolveIndex();
        $actions = $this->body;

        $e = new Event('bulk.execute.before', $indexName);
        $e->actions = $actions;
        EventDispatcher::dispatch($e);

        $start = microtime(true);
        $response = $this->index->getClient()->bulk(
            array_merge(['body' => $actions], $options)
        )->asArray();
        $duration = microtime(true) - $start;

        $this->body = [];
        $this->docCount = 0;
        $this->retryOnConflict = 0;

        $e = new Event('bulk.execute.after', $indexName);
        $e->actions = $actions;
        $e->response = $response;
        $e->duration = $duration;
        EventDispatcher::dispatch($e);

        if (!empty($response['errors'])) {
            if ($this->errorHandler) {
                ($this->errorHandler)($response);
            } else {
                $json = json_encode($response, JSON_UNESCAPED_UNICODE);
                // json_encode() can return false on malformed payloads; guard required
                // under strict_types to avoid passing false to strlen().
                if ($json === false) {
                    $json = '(unable to encode bulk response)';
                }
                if (strlen($json) > 4096) {
                    $json = substr($json, 0, 4096) . '... [truncated]';
                }
                throw new RuntimeException("Bulk request has errors: {$json}");
            }
        }

        return $response;
    }

    /**
     * Resolve the target index name.
     *
     * @return string
     */
    private function resolveIndex(): string
    {
        return $this->targetIndex ?? $this->index->name();
    }

    /**
     * Check auto-flush after each action.
     */
    private function afterPush(): void
    {
        $this->docCount++;

        if ($this->batchSize > 0 && $this->docCount >= $this->batchSize) {
            $this->execute();
        }
    }
}

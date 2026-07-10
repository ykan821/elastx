<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use ElasticKit\Index\Support\Event;
use ElasticKit\Index\Support\EventDispatcher;
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
     * Top-level bulk API params (refresh, timeout, ...) applied to every flush.
     *
     * @var array<string, mixed>
     */
    private array $defaultOptions = [];

    /**
     * @var string|null
     */
    private ?string $targetIndex = null;

    /**
     * Auto-flush threshold (0 = disabled). When set, the buffer is flushed
     * automatically inside the enqueue methods once docCount reaches it.
     *
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
     * @throws \InvalidArgumentException if indexName is empty or starts with a dot (system index)
     */
    public function target(string $indexName): static
    {
        if ($indexName === '') {
            throw new InvalidArgumentException('Target index name must not be empty.');
        }

        if (str_starts_with($indexName, '.')) {
            throw new InvalidArgumentException("System index names (starting with '.') are not allowed: {$indexName}");
        }

        $this->targetIndex = $indexName;

        return $this;
    }

    /**
     * Auto-flush threshold: flush automatically once docCount reaches $size.
     * Off by default (0). When off, the buffer only sends on an explicit flush().
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
     * On error the callback receives three tools and decides what to do:
     * - $response: the raw ES response (items[] carry per-item status/error);
     * - $body: the full original batch in native ES format (successes included);
     * - $newbulk: a fresh Bulk for re-send, inheriting target, options, and retry_on_conflict.
     *
     * Extract the failures from $body using $response (items[k] matches the k-th
     * action), re-enqueue them on $newbulk, and call $newbulk->flush() to retry.
     * Return to consume this batch (cleared), or throw to abort and leave it.
     *
     * @param callable $handler function (array $response, array $body, Bulk $newbulk): void
     * @return $this
     */
    public function onError(callable $handler): static
    {
        $this->errorHandler = $handler;
        return $this;
    }

    /**
     * Set retry_on_conflict for all subsequent update actions. Persists across
     * flush() calls (it's a setting, not per-batch).
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
     * Top-level bulk API params applied to every flush, including the auto-flush
     * triggered by batchSize(). Persists across flush() calls; flush($options)
     * overrides these per-call.
     *
     * @param array<string, mixed> $options
     * @return $this
     */
    public function options(array $options): static
    {
        $this->defaultOptions = $options;

        return $this;
    }

    /**
     * Queue an index (create/overwrite) action.
     *
     * @param string|int|null $id document ID, or null to let ES auto-generate
     * @param array<string, mixed> $data
     * @return $this
     */
    public function index(string|int|null $id, array $data): static
    {
        $action = ['index' => ['_index' => $this->resolveIndex()]];
        if ($id !== null && $id !== '') {
            $action['index']['_id'] = $id;
        }
        $this->body[] = $action;
        $this->body[] = $data;
        $this->afterPush();

        return $this;
    }

    /**
     * Alias for index(). Queue a save (create/overwrite) action.
     *
     * @param string|int|null $id
     * @param array<string, mixed> $data
     * @return $this
     */
    public function save(string|int|null $id, array $data): static
    {
        return $this->index($id, $data);
    }

    /**
     * Queue a create action (fail if document already exists).
     *
     * @param string|int|null $id document ID, or null/'' to let ES auto-generate
     * @param array<string, mixed> $data
     * @return $this
     */
    public function create(string|int|null $id, array $data): static
    {
        $action = ['create' => ['_index' => $this->resolveIndex()]];
        if ($id !== null && $id !== '') {
            $action['create']['_id'] = $id;
        }
        $this->body[] = $action;
        $this->body[] = $data;
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
     * Flush all queued actions to ES and return the raw response.
     *
     * On success the queue is cleared. On error: with an onError handler the
     * batch is handed off (response, the full body, and a fresh Bulk) and cleared
     * on return; without a handler a RuntimeException is thrown and the batch is
     * preserved for the caller to retry. Call this at the end of a batch to flush
     * the remainder — batchSize() auto-flushes full batches during enqueue.
     *
     * @param array<string, mixed> $options top-level bulk API params (refresh, timeout, etc),
     *        overriding the instance-level options() for this flush only
     * @return array<string, mixed>
     * @throws \RuntimeException when the response has errors and no handler swallowed them
     */
    public function flush(array $options = []): array
    {
        if (empty($this->body)) {
            return [];
        }

        $indexName = $this->resolveIndex();
        $actions = $this->body;

        $e = new Event('bulk.flush.before', $indexName);
        $e->actions = $actions;
        EventDispatcher::dispatch($e);

        $start = microtime(true);
        $response = $this->index->getClient()->bulk(
            array_merge(['body' => $actions], $this->defaultOptions, $options)
        )->asArray();
        $duration = microtime(true) - $start;

        $e = new Event('bulk.flush.after', $indexName);
        $e->actions = $actions;
        $e->response = $response;
        $e->duration = $duration;
        EventDispatcher::dispatch($e);

        if (!empty($response['errors'])) {
            if ($this->errorHandler) {
                // Hand the caller the raw materials: the response, the full body,
                // and a fresh Bulk on the same index/target. The caller extracts
                // the failures and re-sends them however it likes.
                // Inherits passive request settings (target, options, retry_on_conflict)
                // so retries reproduce the original request. batchSize/errorHandler stay
                // off: auto-flush would disrupt re-enqueueing, a handler could recurse.
                $newbulk = new Bulk($this->index);
                $newbulk->targetIndex = $this->targetIndex;
                $newbulk->defaultOptions = $this->defaultOptions;
                $newbulk->retryOnConflict = $this->retryOnConflict;
                ($this->errorHandler)($response, $actions, $newbulk);
            } else {
                $json = json_encode($response, JSON_UNESCAPED_UNICODE);
                // json_encode() can return false on malformed payloads; guard required
                // under strict_types to avoid passing false to strlen().
                if ($json === false) {
                    $json = '(unable to encode bulk response)';
                }
                if (strlen($json) > 4096) {
                    $json = mb_strcut($json, 0, 4096) . '... [truncated]';
                }
                throw new RuntimeException("Bulk request has errors: {$json}");
            }
        }

        // Success, or the handler consumed the batch. Only the buffer is reset;
        // retryOnConflict persists across flushes (it's a setting, like target()).
        $this->body = [];
        $this->docCount = 0;

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
            $this->flush();
        }
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\Index;

use BadMethodCallException;
use Elastic\Elasticsearch\ClientInterface;
use ElasticKit\DSL\Query;
use RuntimeException;

/**
 * Abstract base class for index operations and query execution.
 *
 * @phpstan-consistent-constructor
 */
abstract class Index
{
    /**
     * @var string
     */
    protected string $connection = 'default';

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var array<string, mixed>
     */
    protected array $mappings = [];

    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * @var int
     */
    protected int $perPage = 15;

    /**
     * @var int
     */
    protected int $maxPerPage = 100;

    /**
     * Register an Elasticsearch client. Optionally name the connection.
     *
     * @param ClientInterface $client
     * @param string $name connection name, defaults to 'default'
     * @return void
     */
    public static function setClient(ClientInterface $client, string $name = 'default'): void
    {
        ClientManager::set($client, $name);
    }

    /**
     * Return the Elasticsearch client for this index's connection.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return ClientManager::get($this->connection);
    }

    /**
     * Set the connection name for this index instance.
     *
     * @param string $connection
     * @return $this
     */
    public function setConnection(string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Return the connection name for this index.
     *
     * @return string
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * Create a new index instance with the given connection.
     *
     * @param string $connection
     * @return static
     */
    public static function on(string $connection): static
    {
        return (new static())->setConnection($connection);
    }

    /**
     * Return the index name.
     *
     * @return string
     */
    public function name(): string
    {
        if (empty($this->name)) {
            throw new RuntimeException(
                sprintf('Index $name is not set in %s', static::class)
            );
        }

        return $this->name;
    }

    /**
     * Create a new Search instance from this index instance.
     *
     * @param Query|null $query
     * @return Search
     */
    public function newQuery(?Query $query = null): Search
    {
        return new Search($this, $query);
    }

    /**
     * Create a Search instance. Delegates to newQuery() with a fresh instance.
     *
     * @param Query|null $query
     * @return Search
     */
    public static function query(?Query $query = null): Search
    {
        return (new static())->newQuery($query);
    }

    /**
     * Create a Doc reference from this index instance.
     *
     * @param string|int|null $id document id, or null/'' to let ES auto-generate
     * @return Doc
     */
    public function newDoc(string|int|null $id): Doc
    {
        return new Doc($this, $id);
    }

    /**
     * Create a Doc reference. Delegates to newDoc() with a fresh instance.
     *
     * @param string|int|null $id document id, or null/'' to let ES auto-generate
     * @return Doc
     */
    public static function doc(string|int|null $id): Doc
    {
        return (new static())->newDoc($id);
    }

    /**
     * Return the index mapping definition.
     *
     * @return array<string, mixed>
     */
    public function mappings(): array
    {
        return $this->mappings;
    }

    /**
     * Return the index settings definition.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->settings;
    }

    /**
     * Generate the backing index name for rebuild. Override to customize naming.
     *
     * @return string
     */
    public function rebuildName(): string
    {
        return $this->name . '_' . date('Ymd_His');
    }

    /**
     * Return the default number of results per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Return the maximum allowed results per page.
     *
     * @return int
     */
    public function maxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * Yield documents as [id => doc] pairs. Override to provide a default data source for rebuild.
     *
     * @param array<string, mixed> $context user-defined context passed from rebuild
     * @return iterable<string|int, array<string, mixed>>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function source(array $context = []): iterable
    {
        throw new BadMethodCallException(
            'No data source configured. Use Rebuild::source() to provide data.'
        );
    }

}

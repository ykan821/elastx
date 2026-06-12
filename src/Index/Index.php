<?php

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
    protected $connection = 'default';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string, mixed>
     */
    protected $mappings = [];

    /**
     * @var array<string, mixed>
     */
    protected $settings = [];

    /**
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var int
     */
    protected $maxPerPage = 100;

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
    public function setConnection(string $connection)
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
    public static function on(string $connection)
    {
        return (new static())->setConnection($connection);
    }

    /**
     * Return the index name.
     *
     * @return string
     */
    public function name()
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
    public function newQuery(Query $query = null)
    {
        return new Search($this, $query);
    }

    /**
     * Create a Search instance. Delegates to newQuery() with a fresh instance.
     *
     * @param Query|null $query
     * @return Search
     */
    public static function query(Query $query = null)
    {
        return (new static())->newQuery($query);
    }

    /**
     * Create a Doc reference from this index instance.
     *
     * @param string|int $id
     * @return Doc
     */
    public function newDoc($id)
    {
        return new Doc($this, $id);
    }

    /**
     * Create a Doc reference. Delegates to newDoc() with a fresh instance.
     *
     * @param string|int $id
     * @return Doc
     */
    public static function doc($id)
    {
        return (new static())->newDoc($id);
    }

    /**
     * Return the index mapping definition.
     *
     * @return array<string, mixed>
     */
    public function mappings()
    {
        return $this->mappings;
    }

    /**
     * Return the index settings definition.
     *
     * @return array<string, mixed>
     */
    public function settings()
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
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Return the maximum allowed results per page.
     *
     * @return int
     */
    public function maxPerPage()
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

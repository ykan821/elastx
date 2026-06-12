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
     * @var array<string, ClientInterface>
     */
    protected static $clients = [];

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
     * @var callable|null
     */
    protected static $pageResolver;

    /**
     * @var callable|null
     */
    protected static $paginatorResolver;

    /**
     * @var array<string, array<int, callable>>
     */
    protected static $listeners = [];

    /**
     * Register an Elasticsearch client. Optionally name the connection.
     *
     * @param ClientInterface $client
     * @param string|null $name connection name, null for default
     * @return void
     */
    public static function setClient(ClientInterface $client, $name = null)
    {
        self::$clients[$name ?? 'default'] = $client;
    }

    /**
     * Return the Elasticsearch client for this index's connection.
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        if (isset(self::$clients[$this->connection])) {
            return self::$clients[$this->connection];
        }
        if (isset(self::$clients['default'])) {
            return self::$clients['default'];
        }
        throw new RuntimeException(
            'Elasticsearch client not registered for connection \'' . $this->connection . '\'. '
            . 'Call ' . static::class . '::setClient($client) first.'
        );
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
     * Create a new Search instance. Supports both static and instance call.
     *
     * @return Search
     */
    public static function query(Query $query = null)
    {
        return new Search(new static(), $query);
    }

    /**
     * Create a DocReference for a single document. Supports both static and instance call.
     *
     * @param string|int $id
     * @return Doc
     */
    public static function doc($id)
    {
        return new Doc(new static(), $id);
    }

    /**
     * Insert (create or overwrite) a single document.
     *
     * @param string|int|null $id document ID, null or empty string to let ES auto-generate
     * @param array<string, mixed> $document document body
     * @return array<string, mixed>
     */
    public static function insert($id, array $document)
    {
        $index = new static();
        $params = ['index' => $index->name(), 'body' => $document];

        if ($id !== null && $id !== '') {
            $params['id'] = $id;
        }

        return $index->getClient()->index($params)->asArray();
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
     * Register a resolver that extracts page and perPage from the request.
     *
     * @param callable $resolver returns [$page, $perPage]
     * @return void
     */
    public static function setPageResolver(callable $resolver)
    {
        self::$pageResolver = $resolver;
    }

    /**
     * Register a resolver that converts Results into a framework paginator.
     *
     * @param callable $resolver receives (Results $results, int $page, int $perPage)
     * @return void
     */
    public static function setPaginatorResolver(callable $resolver)
    {
        self::$paginatorResolver = $resolver;
    }

    /**
     * Return the registered page resolver, or null.
     *
     * @return callable|null
     */
    public static function getPageResolver()
    {
        return self::$pageResolver;
    }

    /**
     * Return the registered paginator resolver, or null.
     *
     * @return callable|null
     */
    public static function getPaginatorResolver()
    {
        return self::$paginatorResolver;
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

    /**
     * Register an event listener. Supports exact event name, category wildcard (e.g. 'search.*'), or global '*'.
     *
     * @param string $event
     * @param callable $listener receives (Event $event)
     * @return void
     */
    public static function listen($event, callable $listener)
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event to all matching listeners.
     *
     * @param Event $event
     * @return void
     */
    public static function dispatch(Event $event)
    {
        foreach (self::$listeners as $pattern => $listeners) {
            if ($pattern === $event->name || $pattern === '*' || static::matchesCategory($pattern, $event->name)) {
                foreach ($listeners as $listener) {
                    $listener($event);
                }
            }
        }
    }

    /**
     * Check if a wildcard pattern matches an event by category.
     *
     * @param string $pattern
     * @param string $event
     * @return bool
     */
    protected static function matchesCategory($pattern, $event)
    {
        if (substr($pattern, -2) !== '.*') {
            return false;
        }

        $prefix = substr($pattern, 0, -1);
        return strpos($event, $prefix) === 0;
    }
}

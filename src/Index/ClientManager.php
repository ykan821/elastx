<?php

namespace ElasticKit\Index;

use Elastic\Elasticsearch\ClientInterface;
use RuntimeException;

/**
 * Manages Elasticsearch client connections.
 */
class ClientManager
{
    /**
     * @var array<string, ClientInterface>
     */
    private static $clients = [];

    /**
     * Register an Elasticsearch client. Optionally name the connection.
     *
     * @param ClientInterface $client
     * @param string|null $name connection name, null for default
     * @return void
     */
    public static function set(ClientInterface $client, $name = null)
    {
        self::$clients[$name ?? 'default'] = $client;
    }

    /**
     * Return the Elasticsearch client for the given connection name.
     *
     * Falls back to 'default' if the named connection is not registered.
     *
     * @param string $connection
     * @return ClientInterface
     * @throws RuntimeException if no client is registered
     */
    public static function get($connection = 'default')
    {
        if (isset(self::$clients[$connection])) {
            return self::$clients[$connection];
        }
        if (isset(self::$clients['default'])) {
            return self::$clients['default'];
        }
        throw new RuntimeException(
            "Elasticsearch client not registered for connection '{$connection}'. "
            . 'Call ClientManager::set($client) first.'
        );
    }

    /**
     * Reset all registered clients. Mainly for testing.
     *
     * @return void
     */
    public static function reset()
    {
        self::$clients = [];
    }
}

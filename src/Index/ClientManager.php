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
     * @param string $name connection name, defaults to 'default'
     * @return void
     */
    public static function set(ClientInterface $client, string $name = 'default'): void
    {
        self::$clients[$name] = $client;
    }

    /**
     * Return the Elasticsearch client for the given connection name.
     *
     * @param string $connection
     * @return ClientInterface
     * @throws RuntimeException if the connection is not registered
     */
    public static function get(string $connection = 'default'): ClientInterface
    {
        if (isset(self::$clients[$connection])) {
            return self::$clients[$connection];
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
    public static function reset(): void
    {
        self::$clients = [];
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\Index\Support;

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
    private static array $clients = [];

    /**
     * Lazy builders, invoked once on first get() per connection.
     *
     * @var array<string, callable(): ClientInterface>
     */
    private static array $resolvers = [];

    /**
     * Register an Elasticsearch client. Optionally name the connection.
     * Replaces any resolver previously set for this connection.
     *
     * @param ClientInterface $client
     * @param string $connection connection name, defaults to 'default'
     * @return void
     */
    public static function set(ClientInterface $client, string $connection = 'default'): void
    {
        self::$clients[$connection] = $client;
        unset(self::$resolvers[$connection]);
    }

    /**
     * Register a resolver that builds the client lazily on first get().
     *
     * The built client is memoized, so the resolver runs at most once per
     * connection until reset() or a later set()/setResolver() replaces it.
     *
     * @param callable(): ClientInterface $resolver
     * @param string $connection connection name, defaults to 'default'
     * @return void
     */
    public static function setResolver(callable $resolver, string $connection = 'default'): void
    {
        self::$resolvers[$connection] = $resolver;
        unset(self::$clients[$connection]);
    }

    /**
     * Return the Elasticsearch client for the given connection name.
     *
     * If only a resolver is registered, it is invoked once and the result is
     * cached for subsequent calls.
     *
     * @param string $connection
     * @return ClientInterface
     * @throws RuntimeException if no client or resolver is registered
     */
    public static function get(string $connection = 'default'): ClientInterface
    {
        if (isset(self::$clients[$connection])) {
            return self::$clients[$connection];
        }
        if (isset(self::$resolvers[$connection])) {
            $client = (self::$resolvers[$connection])();
            self::$clients[$connection] = $client;

            return $client;
        }
        throw new RuntimeException(
            "Elasticsearch client not registered for connection '{$connection}'. "
            . 'Call ClientManager::set($client) or ClientManager::setResolver($resolver) first.'
        );
    }

    /**
     * Reset all registered clients and resolvers. Mainly for testing.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$clients = [];
        self::$resolvers = [];
    }
}

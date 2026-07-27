<?php

declare(strict_types=1);

namespace Tests;

use Elastic\Elasticsearch\ClientInterface;
use ElasticKit\Index\Support\ClientManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ClientManager registration, lazy resolution, and memoization.
 */
class ClientManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        ClientManager::reset();
    }

    public function testResolverBuildsClientLazily(): void
    {
        $calls = 0;
        $client = $this->createMock(ClientInterface::class);

        ClientManager::setResolver(function () use (&$calls, $client) {
            $calls++;
            return $client;
        });

        self::assertSame(0, $calls, 'resolver must not run at registration');
        self::assertSame($client, ClientManager::get());
        self::assertSame(1, $calls, 'resolver must run on first get');
    }

    public function testResolverResultIsMemoized(): void
    {
        $calls = 0;
        ClientManager::setResolver(function () use (&$calls) {
            $calls++;
            return $this->createMock(ClientInterface::class);
        });

        ClientManager::get();
        ClientManager::get();
        ClientManager::get();

        self::assertSame(1, $calls, 'resolver must run only once');
    }

    public function testConcreteSetOverridesResolver(): void
    {
        $resolved = $this->createMock(ClientInterface::class);
        $concrete = $this->createMock(ClientInterface::class);

        ClientManager::setResolver(fn () => $resolved);
        ClientManager::set($concrete);

        self::assertSame($concrete, ClientManager::get());
    }

    public function testSetResolverAfterSetRebuilds(): void
    {
        $first = $this->createMock(ClientInterface::class);
        $second = $this->createMock(ClientInterface::class);

        ClientManager::set($first);
        ClientManager::setResolver(fn () => $second);

        self::assertSame($second, ClientManager::get());
    }

    public function testNamedConnectionsResolveIndependently(): void
    {
        $main = $this->createMock(ClientInterface::class);
        $logs = $this->createMock(ClientInterface::class);

        ClientManager::setResolver(fn () => $main, 'main');
        ClientManager::setResolver(fn () => $logs, 'logs');

        self::assertSame($main, ClientManager::get('main'));
        self::assertSame($logs, ClientManager::get('logs'));
    }

    public function testResetClearsResolver(): void
    {
        ClientManager::setResolver(fn () => $this->createMock(ClientInterface::class));
        ClientManager::reset();

        $this->expectException(RuntimeException::class);
        ClientManager::get();
    }

    public function testGetWithoutRegistrationThrows(): void
    {
        $this->expectException(RuntimeException::class);
        ClientManager::get('missing');
    }
}

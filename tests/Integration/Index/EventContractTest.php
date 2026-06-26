<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use ElasticKit\Index\Bulk;
use ElasticKit\Index\Support\Event;
use ElasticKit\Index\Support\EventDispatcher;
use Tests\Integration\IntegrationTestCase;

class EventContractTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        EventDispatcher::reset();
        parent::tearDown();
    }

    public function testSearchEvents(): void
    {
        $events = [];
        EventDispatcher::listen('search.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });
        $this->makeIndex()->newQuery()->matchAll()->get();
        $this->assertContains('search.query.before', $events);
        $this->assertContains('search.query.after', $events);
    }

    public function testBulkEvents(): void
    {
        $events = [];
        EventDispatcher::listen('bulk.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });
        (new Bulk($this->makeIndex()))->index('10', ['title' => 'x'])->flush();
        $this->assertContains('bulk.flush.before', $events);
        $this->assertContains('bulk.flush.after', $events);
    }

    public function testMultipleListeners(): void
    {
        $count = 0;
        EventDispatcher::listen('search.query.before', function () use (&$count) {
            $count++;
        });
        EventDispatcher::listen('search.query.before', function () use (&$count) {
            $count++;
        });
        $this->makeIndex()->newQuery()->matchAll()->get();
        $this->assertSame(2, $count);
    }

    public function testNoListenersDoesNotError(): void
    {
        $this->makeIndex()->newQuery()->matchAll()->get();
        $this->assertTrue(true);
    }
}

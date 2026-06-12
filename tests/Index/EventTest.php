<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Event;
use ElasticKit\Index\EventDispatcher;
use ElasticKit\Index\Index;

class EventTest extends TestCase
{
    protected function tearDown(): void
    {
        EventDispatcher::reset();
        ClientManager::reset();
    }

    protected function createIndex($name = 'products')
    {
        return new class($name) extends Index {
            public function __construct($name = 'products')
            {
                $this->name = $name;
            }
        };
    }

    protected function mockClient()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]));
        Index::setClient($client);
        return $client;
    }

    public function testListenAndDispatch()
    {
        $received = null;
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$received) {
            $received = $e;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertNotNull($received);
        $this->assertEquals('search.query.before', $received->name);
        $this->assertEquals('products', $received->index);
    }

    public function testSearchBeforePassesDsl()
    {
        $dsl = null;
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$dsl) {
            $dsl = $e->dsl;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->match('title', 'test')->get();

        $this->assertIsArray($dsl);
        $this->assertArrayHasKey('query', $dsl);
    }

    public function testSearchAfterPassesResponse()
    {
        $response = null;
        EventDispatcher::listen('search.query.after', function (Event $e) use (&$response) {
            $response = $e->response;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('hits', $response);
    }

    public function testSearchAfterContainsDuration()
    {
        $duration = null;
        EventDispatcher::listen('search.query.after', function (Event $e) use (&$duration) {
            $duration = $e->duration;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertIsFloat($duration);
        $this->assertGreaterThanOrEqual(0, $duration);
    }

    public function testSearchEventPassesAction()
    {
        $action = null;
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$action) {
            $action = $e->action;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertEquals('get', $action);
    }

    public function testFirstTriggersSearchWithAction()
    {
        $action = null;
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$action) {
            $action = $e->action;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->first();

        $this->assertEquals('first', $action);
    }

    public function testWildcardListener()
    {
        $events = [];
        EventDispatcher::listen('*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertContains('search.query.before', $events);
        $this->assertContains('search.query.after', $events);
    }

    public function testCategoryWildcardListener()
    {
        $events = [];
        EventDispatcher::listen('search.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertContains('search.query.before', $events);
        $this->assertContains('search.query.after', $events);
    }

    public function testCategoryWildcardMatchesSearchQueryEvents()
    {
        $events = [];
        EventDispatcher::listen('search.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]);
        $client->method('count')->willReturn(new ArrayResponse(['count' => 0]));
        Index::setClient($client);

        $index = $this->createIndex();
        $index->query()->count();

        $this->assertNotEmpty($events);
        $this->assertContains('search.query.before', $events);
        $this->assertContains('search.query.after', $events);
    }

    public function testMultipleListeners()
    {
        $count = 0;
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$count) {
            $count++;
        });
        EventDispatcher::listen('search.query.before', function (Event $e) use (&$count) {
            $count++;
        });

        $this->mockClient();
        $index = $this->createIndex();
        $index->query()->get();

        $this->assertEquals(2, $count);
    }

    public function testNoListenersDoesNotError()
    {
        $this->mockClient();
        $index = $this->createIndex();

        $index->query()->get();
        $this->assertTrue(true);
    }

    public function testBulkExecutePassesActions()
    {
        $actions = null;
        EventDispatcher::listen('bulk.execute.before', function (Event $e) use (&$actions) {
            $actions = $e->actions;
        });

        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturn(new ArrayResponse(['errors' => false]));
        Index::setClient($client);

        $index = $this->createIndex();
        $bulk = new \ElasticKit\Index\Bulk($index);
        $bulk->index(1, ['title' => 'test']);
        $bulk->execute();

        $this->assertIsArray($actions);
        $this->assertCount(2, $actions);
    }

    public function testManagerCreateEvents()
    {
        $events = [];
        EventDispatcher::listen('manager.create.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex();
        $manager = new \ElasticKit\Index\Manager($index);
        $manager->create();

        $this->assertContains('manager.create.before', $events);
        $this->assertContains('manager.create.after', $events);
    }

    public function testManagerDeleteEvents()
    {
        $events = [];
        EventDispatcher::listen('manager.delete.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $indices = $this->createMock(TestIndices::class);
        $indices->method('delete')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex();
        $manager = new \ElasticKit\Index\Manager($index);
        $manager->delete();

        $this->assertContains('manager.delete.before', $events);
        $this->assertContains('manager.delete.after', $events);
    }

    public function testManagerReadOperationsHaveNoEvents()
    {
        $events = [];
        EventDispatcher::listen('*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $indices = $this->createMock(TestIndices::class);
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('get')->willReturn(new ArrayResponse([]));
        $indices->method('getMapping')->willReturn(new ArrayResponse([]));
        $indices->method('getSettings')->willReturn(new ArrayResponse([]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex();
        $manager = new \ElasticKit\Index\Manager($index);
        $manager->exists();
        $manager->get();
        $manager->getMapping();
        $manager->getSettings();

        $this->assertEmpty($events);
    }

    public function testRebuildRunBeforeAndAfterEvents()
    {
        $events = [];
        EventDispatcher::listen('rebuild.*', function (Event $e) use (&$events) {
            $events[] = $e->name;
        });

        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturn(new BoolResponse(false));
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('bulk')->willReturn(new ArrayResponse(['errors' => false]));
        Index::setClient($client);

        $index = $this->createIndex();
        $rebuild = new \ElasticKit\Index\Rebuild($index);
        $rebuild->source(function () { yield 1 => ['title' => 'test']; });
        $rebuild->run();

        $this->assertContains('rebuild.run.before', $events);
        $this->assertContains('rebuild.run.after', $events);
    }
}

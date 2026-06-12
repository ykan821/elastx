<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Index;
use ElasticKit\Index\Rebuild;

class RebuildTest extends TestCase
{
    protected function setUp(): void
    {
        Index::setClient($this->createMock(TestClient::class));
    }

    protected function tearDown(): void
    {
        ClientManager::reset();
    }

    protected function createIndex($name = 'products', $mappings = [], $settings = [])
    {
        return new class($name, $mappings, $settings) extends Index {
            public function __construct($name, $mappings, $settings)
            {
                $this->name = $name;
                $this->mappings = $mappings;
                $this->settings = $settings;
            }
        };
    }

    public function testRunCreatesBackingIndexAndSetsAlias()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->expects($this->once())->method('create')->with($this->callback(function ($params) {
            return strpos($params['index'], 'products_') === 0
                && $params['body']['mappings'] === ['properties' => ['title' => ['type' => 'text']]]
                && $params['body']['settings'] === ['number_of_shards' => 1];
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->expects($this->once())->method('putAlias')->with($this->callback(function ($params) {
            return strpos($params['index'], 'products_') === 0 && $params['name'] === 'products';
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->expects($this->once())->method('bulk')->with($this->callback(function ($params) {
            return count($params['body']) === 4
                && $params['body'][0]['index']['_id'] === 1
                && $params['body'][2]['index']['_id'] === 2;
        }))->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
                $this->mappings = ['properties' => ['title' => ['type' => 'text']]];
                $this->settings = ['number_of_shards' => 1];
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
                yield 2 => ['title' => 'B'];
            }
        };

        $result = (new Rebuild($index))->run();
        $this->assertStringStartsWith('products_', $result['newIndex']);
        $this->assertNull($result['oldIndex']);
    }

    public function testRunSwapsAliasAtomically()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->expects($this->once())->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('existsAlias')->willReturn(new BoolResponse(true));
        $indices->method('getAlias')->willReturn(new ArrayResponse(['products_v1' => ['aliases' => ['products' => []]]]));
        $indices->expects($this->once())->method('updateAliases')->with($this->callback(function ($params) {
            $actions = $params['body']['actions'];
            return $actions[0]['remove']['index'] === 'products_v1'
                && $actions[0]['remove']['alias'] === 'products'
                && strpos($actions[1]['add']['index'], 'products_') === 0
                && $actions[1]['add']['alias'] === 'products';
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        $result = (new Rebuild($index))->allowEmpty()->run();
        $this->assertEquals('products_v1', $result['oldIndex']);
    }

    public function testRunThrowsWhenNameIsRealIndex()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('delete')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is a real index, not an alias');
        (new Rebuild($index))->allowEmpty()->run();
    }

    public function testRunWithBatchSize()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->expects($this->exactly(2))->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
                yield 2 => ['title' => 'B'];
                yield 3 => ['title' => 'C'];
            }
        };

        (new Rebuild($index))->batchSize(2)->run();
    }

    public function testRunWithCustomSource()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->expects($this->once())->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');

        $result = (new Rebuild($index))->source([
            1 => ['title' => 'A'],
            2 => ['title' => 'B'],
        ])->run();

        $this->assertStringStartsWith('products_', $result['newIndex']);
    }

    public function testRunWithCustomRealName()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->expects($this->once())->method('create')->with($this->callback(function ($params) {
            return strpos($params['index'], 'products_v') === 0;
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function rebuildName(): string
            {
                return $this->name . '_v' . time();
            }
        };

        $result = (new Rebuild($index))->source(function () { return []; })->allowEmpty()->run();
    }

    public function testCleanDeletesSpecificIndex()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->expects($this->once())->method('delete')->with($this->callback(function ($params) {
            return $params['index'] === 'products_20250522_090000';
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Rebuild($index))->clean('products_20250522_090000');
    }

    public function testRollbackToSpecificIndex()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('getAlias')->willReturnCallback(function ($params) {
            if (($params['name'] ?? null) === 'products') {
                return new ArrayResponse(['products_20250523_143000' => ['aliases' => ['products' => []]]]);
            }
            return new ArrayResponse([]);
        });
        $indices->expects($this->once())->method('updateAliases')->with($this->callback(function ($params) {
            $actions = $params['body']['actions'];
            return $actions[0]['remove']['index'] === 'products_20250523_143000'
                && $actions[0]['remove']['alias'] === 'products'
                && $actions[1]['add']['index'] === 'products_20250520_080000'
                && $actions[1]['add']['alias'] === 'products';
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex('products');
        $rolledBack = (new Rebuild($index))->rollback('products_20250520_080000');

        $this->assertEquals('products_20250523_143000', $rolledBack);
    }

    public function testRollbackThrowsWhenNoAlias()
    {
        $this->expectException(\RuntimeException::class);

        $indices = $this->createMock(TestIndices::class);
        $indices->method('getAlias')->willReturn(new ArrayResponse([]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Rebuild($index))->rollback('products_20250520_080000');
    }

    public function testRollbackThrowsWhenTargetNotExist()
    {
        $this->expectException(\RuntimeException::class);

        $indices = $this->createMock(TestIndices::class);
        $indices->method('exists')->willReturn(new BoolResponse(false));
        $indices->method('getAlias')->willReturnCallback(function ($params) {
            if (($params['name'] ?? null) === 'products') {
                return new ArrayResponse(['products_20250523_143000' => ['aliases' => ['products' => []]]]);
            }
            return new ArrayResponse([]);
        });

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Rebuild($index))->rollback('products_20250520_080000');
    }

    public function testRunThrowsOnEmptyImport()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('delete')->with($this->callback(function ($params) {
            return strpos($params['index'], 'products_') === 0;
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rebuild imported 0 documents');
        (new Rebuild($index))->run();
    }

    public function testRunAllowsEmptyImportWithAllowEmpty()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        $result = (new Rebuild($index))->allowEmpty()->run();
        $this->assertStringStartsWith('products_', $result['newIndex']);
    }

    public function testRunDeletesNewIndexOnImportFailure()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->expects($this->once())->method('delete')->with($this->callback(function ($params) {
            return strpos($params['index'], 'products_') === 0;
        }))->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => [], 'errors' => true]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
            }
        };

        $this->expectException(\RuntimeException::class);
        (new Rebuild($index))->run();
    }

    public function testRunAcquiresAndReleasesLock()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(true);
            }
            return new BoolResponse(false);
        });
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        $client->expects($this->once())->method('index')->with($this->callback(function ($params) {
            return $params['index'] === '.ek_locks'
                && $params['id'] === 'products'
                && ($params['op_type'] ?? null) === 'create';
        }))->willReturn(new ArrayResponse(['result' => 'created']));
        $client->expects($this->once())->method('delete')->with($this->callback(function ($params) {
            return $params['index'] === '.ek_locks' && $params['id'] === 'products';
        }))->willReturn(new ArrayResponse(['result' => 'deleted']));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        (new Rebuild($index))->allowEmpty()->run();
    }

    public function testRunReleasesLockOnFailure()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(true));
        $indices->method('delete')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $lockReleased = false;
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturnCallback(function () use (&$lockReleased) {
            $lockReleased = true;
            return new ArrayResponse(['result' => 'deleted']);
        });
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => [], 'errors' => true]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
            }
        };

        try {
            (new Rebuild($index))->run();
        } catch (\RuntimeException $e) {
            // Expected: bulk import error
        }

        $this->assertTrue($lockReleased, 'Lock should be released even when run() fails');
    }

    public function testRunThrowsWhenLocked()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('exists')->willReturn(new BoolResponse(true));

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(409);

        $exception = new \Elastic\Elasticsearch\Exception\ClientResponseException();
        $exception->setResponse($response);

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willThrowException($exception);
        Index::setClient($client);

        $index = $this->createIndex('products');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already running');
        (new Rebuild($index))->source([1 => ['title' => 'A']])->run();
    }

    public function testForceUnlock()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())->method('delete')->with($this->callback(function ($params) {
            return $params['index'] === '.ek_locks' && $params['id'] === 'products';
        }))->willReturn(new ArrayResponse(['result' => 'deleted']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Rebuild($index))->forceUnlock();
    }

    public function testForceUnlockIsIdempotent()
    {
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $exception = new \Elastic\Elasticsearch\Exception\ClientResponseException();
        $exception->setResponse($response);

        $client = $this->createMock(TestClient::class);
        $client->method('delete')->willThrowException($exception);
        Index::setClient($client);

        $index = $this->createIndex('products');

        // Should not throw despite 404
        (new Rebuild($index))->forceUnlock();
        $this->assertTrue(true); // Reached without exception
    }

    public function testIsLockedReturnsTrue()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('exists')->with($this->callback(function ($params) {
            return $params['index'] === '.ek_locks' && $params['id'] === 'products';
        }))->willReturn(new BoolResponse(true));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertTrue((new Rebuild($index))->isLocked());
    }

    public function testIsLockedReturnsFalse()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('exists')->willReturn(new BoolResponse(false));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertFalse((new Rebuild($index))->isLocked());
    }

    public function testEnsureLockIndexCreatesWhenNotExists()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('exists')->willReturnCallback(function ($params) {
            if (($params['index'] ?? '') === '.ek_locks') {
                return new BoolResponse(false);
            }
            return new BoolResponse(false);
        });
        $indices->method('create')->willReturnCallback(function ($params) {
            return new ArrayResponse(['acknowledged' => true]);
        });
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $lockIndexCreated = false;
        $indices->expects($this->exactly(2))->method('create')->willReturnCallback(function ($params) use (&$lockIndexCreated) {
            if (($params['index'] ?? '') === '.ek_locks') {
                $lockIndexCreated = true;
                $this->assertEquals(1, $params['body']['settings']['number_of_shards'] ?? 0);
                $this->assertEquals(0, $params['body']['settings']['number_of_replicas'] ?? 1);
            }
            return new ArrayResponse(['acknowledged' => true]);
        });

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse(['items' => []]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        (new Rebuild($index))->allowEmpty()->run();
    }

    public function testOnErrorReceivesResponse()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(false));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $errorResponse = [
            'errors' => true,
            'items' => [
                ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ],
        ];
        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse($errorResponse));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
            }
        };

        $received = null;
        (new Rebuild($index))
            ->onError(function ($response) use (&$received) {
                $received = $response;
            })
            ->run();

        $this->assertEquals($errorResponse, $received);
    }

    public function testOnErrorPreventsException()
    {
        $indices = $this->createMock(TestIndices::class);
        $indices->method('create')->willReturn(new ArrayResponse(['acknowledged' => true]));
        $indices->method('exists')->willReturn(new BoolResponse(false));
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->method('putAlias')->willReturn(new ArrayResponse(['acknowledged' => true]));

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        $client->method('index')->willReturn(new ArrayResponse(['result' => 'created']));
        $client->method('delete')->willReturn(new ArrayResponse(['result' => 'deleted']));
        $client->method('bulk')->willReturn(new ArrayResponse([
            'errors' => true,
            'items' => [],
        ]));
        Index::setClient($client);

        $index = new class extends Index {
            public function __construct()
            {
                $this->name = 'products';
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
            }
        };

        // Without onError, would throw. With onError (empty callback), completes successfully.
        $result = (new Rebuild($index))
            ->onError(function () {})
            ->run();

        $this->assertStringStartsWith('products_', $result['newIndex']);
    }
}

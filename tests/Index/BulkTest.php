<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\Bulk;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Index;

class BulkTest extends TestCase
{
    protected function setUp(): void
    {
        Index::setClient($this->createMock(TestClient::class));
    }

    protected function tearDown(): void
    {
        ClientManager::reset();
    }

    protected function createIndex($name = 'products')
    {
        return new class($name) extends Index {
            public function __construct($name)
            {
                $this->name = $name;
            }
        };
    }

    public function testIndexAction()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['index' => ['_index' => 'products', '_id' => '1']],
                    ['title' => 'foo'],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = (new Bulk($index))->index('1', ['title' => 'foo'])->execute();

        $this->assertFalse($result['errors']);
    }

    public function testCreateAction()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['create' => ['_index' => 'products', '_id' => '1']],
                    ['title' => 'foo'],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->create('1', ['title' => 'foo'])->execute();
    }

    public function testUpdateAction()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['update' => ['_index' => 'products', '_id' => '1']],
                    ['doc' => ['title' => 'updated'], 'doc_as_upsert' => false],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->update('1', ['title' => 'updated'])->execute();
    }

    public function testUpdateWithoutUpsert()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['update' => ['_index' => 'products', '_id' => '1']],
                    ['doc' => ['title' => 'updated'], 'doc_as_upsert' => false],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->update('1', ['title' => 'updated'], false)->execute();
    }

    public function testUpdateWithRetryOnConflict()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['update' => ['_index' => 'products', '_id' => '1', 'retry_on_conflict' => 3]],
                    ['doc' => ['title' => 'updated'], 'doc_as_upsert' => false],
                    ['update' => ['_index' => 'products', '_id' => '2', 'retry_on_conflict' => 3]],
                    ['doc' => ['title' => 'bar'], 'doc_as_upsert' => false],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))
            ->retryOnConflict(3)
            ->update('1', ['title' => 'updated'])
            ->update('2', ['title' => 'bar'])
            ->execute();
    }

    public function testDeleteAction()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['delete' => ['_index' => 'products', '_id' => '1']],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->delete('1')->execute();
    }

    public function testMixedActions()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['index' => ['_index' => 'products', '_id' => '1']],
                    ['title' => 'foo'],
                    ['update' => ['_index' => 'products', '_id' => '2']],
                    ['doc' => ['title' => 'bar'], 'doc_as_upsert' => false],
                    ['delete' => ['_index' => 'products', '_id' => '3']],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))
            ->index('1', ['title' => 'foo'])
            ->update('2', ['title' => 'bar'])
            ->delete('3')
            ->execute();
    }

    public function testExecuteWithOptions()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['index' => ['_index' => 'products', '_id' => '1']],
                    ['title' => 'foo'],
                ],
                'refresh' => 'wait_for',
                'timeout' => '5s',
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))
            ->index('1', ['title' => 'foo'])
            ->execute(['refresh' => 'wait_for', 'timeout' => '5s']);
    }

    public function testExecuteClearsBodyAndRetryOnConflict()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function ($params) use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                $this->assertEquals(3, $params['body'][0]['update']['retry_on_conflict']);
                $this->assertEquals('wait_for', $params['refresh']);
            } else {
                $this->assertArrayNotHasKey('retry_on_conflict', $params['body'][0]['update']);
                $this->assertArrayNotHasKey('refresh', $params);
            }
            return new ArrayResponse(['errors' => false, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = new Bulk($index);

        $bulk->retryOnConflict(3)->update('1', ['title' => 'first'])->execute(['refresh' => 'wait_for']);
        $bulk->update('1', ['title' => 'second'])->execute();

        $this->assertEquals(2, $callCount);
    }

    public function testTargetOverridesIndexName()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['index' => ['_index' => 'products_new', '_id' => '1']],
                    ['title' => 'foo'],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->target('products_new')->index('1', ['title' => 'foo'])->execute();
    }

    public function testAutoFlushTriggersExecute()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            return new ArrayResponse(['errors' => false, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))->batchSize(2);

        $bulk->index('1', ['title' => 'a']);
        $this->assertEquals(0, $callCount);

        $bulk->index('2', ['title' => 'b']);
        $this->assertEquals(1, $callCount);

        $bulk->index('3', ['title' => 'c']);
        $bulk->execute();
        $this->assertEquals(2, $callCount);
    }

    public function testExecuteReturnsEmptyWhenBodyIsEmpty()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->never())->method('bulk');
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = (new Bulk($index))->execute();

        $this->assertEquals([], $result);
    }

    public function testExecuteThrowsOnErrors()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturn(new ArrayResponse([
            'errors' => true,
            'items' => [
                ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->expectException(\RuntimeException::class);
        (new Bulk($index))->index('1', ['title' => 'foo'])->execute();
    }

    public function testExecuteCallsOnError()
    {
        $client = $this->createMock(TestClient::class);
        $errorResponse = [
            'errors' => true,
            'items' => [
                ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ],
        ];
        $client->method('bulk')->willReturn(new ArrayResponse($errorResponse));
        Index::setClient($client);

        $received = null;
        $index = $this->createIndex('products');
        (new Bulk($index))
            ->onError(function ($response) use (&$received) {
                $received = $response;
            })
            ->index('1', ['title' => 'foo'])
            ->execute();

        $this->assertEquals($errorResponse, $received);
    }

    public function testAutoFlushCallsOnError()
    {
        $errorResponse = [
            'errors' => true,
            'items' => [
                ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ],
        ];
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturn(new ArrayResponse($errorResponse));
        Index::setClient($client);

        $received = null;
        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))
            ->batchSize(1)
            ->onError(function ($response) use (&$received) {
                $received = $response;
            });

        $bulk->index('1', ['title' => 'foo']); // triggers auto-flush

        $this->assertEquals($errorResponse, $received);
    }

    public function testAutoFlushThrowsOnErrorsWithoutHandler()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturn(new ArrayResponse([
            'errors' => true,
            'items' => [],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))->batchSize(1);

        $this->expectException(\RuntimeException::class);
        $bulk->index('1', ['title' => 'foo']); // auto-flush throws
    }
}

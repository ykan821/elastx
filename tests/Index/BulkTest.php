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
        $result = (new Bulk($index))->index('1', ['title' => 'foo'])->flush();

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
        (new Bulk($index))->create('1', ['title' => 'foo'])->flush();
    }

    public function testCreateActionWithoutIdOmitsId()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('bulk')
            ->with([
                'body' => [
                    ['create' => ['_index' => 'products']], // no _id → ES auto-generates
                    ['title' => 'foo'],
                ],
            ])
            ->willReturn(new ArrayResponse(['errors' => false, 'items' => []]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))->create(null, ['title' => 'foo'])->flush();
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
        (new Bulk($index))->update('1', ['title' => 'updated'])->flush();
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
        (new Bulk($index))->update('1', ['title' => 'updated'], false)->flush();
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
            ->flush();
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
        (new Bulk($index))->delete('1')->flush();
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
            ->flush();
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
            ->flush(['refresh' => 'wait_for', 'timeout' => '5s']);
    }

    public function testFlushClearsBodyButPersistsRetryOnConflict()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function ($params) use (&$callCount) {
            $callCount++;
            // retryOnConflict persists across flushes (setting); refresh is per-call
            $this->assertSame(3, $params['body'][0]['update']['retry_on_conflict']);
            if ($callCount === 1) {
                $this->assertSame('wait_for', $params['refresh']);
            } else {
                $this->assertArrayNotHasKey('refresh', $params);
            }
            return new ArrayResponse(['errors' => false, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = new Bulk($index);

        $bulk->retryOnConflict(3)->update('1', ['title' => 'first'])->flush(['refresh' => 'wait_for']);
        $bulk->update('1', ['title' => 'second'])->flush();

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
        (new Bulk($index))->target('products_new')->index('1', ['title' => 'foo'])->flush();
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
        $bulk->flush();
        $this->assertEquals(2, $callCount);
    }

    public function testExecuteReturnsEmptyWhenBodyIsEmpty()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->never())->method('bulk');
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = (new Bulk($index))->flush();

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
        (new Bulk($index))->index('1', ['title' => 'foo'])->flush();
    }

    public function testExecutePreservesBodyForRetryOnError()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return new ArrayResponse(['errors' => true, 'items' => [
                    ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
                ]]);
            }
            return new ArrayResponse(['errors' => false, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))->index('1', ['title' => 'foo']);

        try {
            $bulk->flush();
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            // body must survive the failure so the caller can retry
        }

        $result = $bulk->flush();
        $this->assertFalse($result['errors']);
        $this->assertEquals(2, $callCount);
    }

    public function testOnErrorReceivesResponseBodyAndFreshBulk()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturn(new ArrayResponse([
            'errors' => true,
            'items' => [
                ['index' => ['_id' => '1', 'status' => 201]],
                ['index' => ['_id' => '2', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ],
        ]));
        Index::setClient($client);

        $captured = [];
        $outer = (new Bulk($this->createIndex('products')))
            ->target('products_new')
            ->onError(function ($response, $body, $newbulk) use (&$captured) {
                $captured['response'] = $response;
                $captured['body'] = $body;
                $captured['newbulk'] = $newbulk;
            });
        $outer->index('1', ['title' => 'A'])->index('2', ['title' => 'B'])->flush();

        $this->assertTrue($captured['response']['errors']);              // raw ES response
        $this->assertSame('1', $captured['body'][0]['index']['_id']);   // full body, successes included
        $this->assertSame(['title' => 'A'], $captured['body'][1]);
        $this->assertSame('2', $captured['body'][2]['index']['_id']);
        $this->assertInstanceOf(Bulk::class, $captured['newbulk']);     // fresh Bulk
        $this->assertNotSame($outer, $captured['newbulk']);             // independent instance
    }

    public function testOnErrorRetriesFailuresViaFreshBulkOnSameTarget()
    {
        // Outer targets 'products_new'. Batch of 2: id=1 succeeds, id=2 fails.
        $calls = [];
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function ($params) use (&$calls) {
            $calls[] = $params['body'];
            if (count($calls) === 1) {
                return new ArrayResponse([
                    'errors' => true,
                    'items' => [
                        ['index' => ['_id' => '1', 'status' => 201]],
                        ['index' => ['_id' => '2', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
                    ],
                ]);
            }
            return new ArrayResponse(['errors' => false, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        (new Bulk($index))
            ->target('products_new')
            ->onError(function ($response, $body, $newbulk) {
                // user extracts the failure (id=2) and re-sends it on the fresh bulk.
                // items[k] ↔ k-th action; for an all-index batch, action k's data is body[2k+1].
                foreach ($response['items'] as $i => $item) {
                    if (($item['index']['status'] ?? 200) >= 400) {
                        $newbulk->index($item['index']['_id'], $body[$i * 2 + 1]);
                    }
                }
                $newbulk->flush();
            })
            ->index('1', ['title' => 'A'])
            ->index('2', ['title' => 'B'])
            ->flush();

        $this->assertCount(2, $calls);                                      // original + retry
        $this->assertSame('products_new', $calls[1][0]['index']['_index']); // fresh bulk inherited target
        $this->assertSame('2', $calls[1][0]['index']['_id']);               // only the failure
        $this->assertSame(['title' => 'B'], $calls[1][1]);                  // its data
    }

    public function testOnErrorFreshBulkHasNoHandlerSoItsErrorsThrow()
    {
        // The fresh Bulk is bare (no handler): its own flush() throws on error
        // rather than recursing back into the handler.
        $calls = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function () use (&$calls) {
            $calls++;
            return new ArrayResponse(['errors' => true, 'items' => [
                ['index' => ['_id' => '1', 'status' => 400, 'error' => ['type' => 'mapper_parsing_exception']]],
            ]]);
        });
        Index::setClient($client);

        $threw = false;
        $index = $this->createIndex('products');
        (new Bulk($index))
            ->onError(function ($response, $body, $newbulk) use (&$threw) {
                $newbulk->index('1', ['title' => 'retry']);
                try {
                    $newbulk->flush();
                } catch (\RuntimeException $e) {
                    $threw = true; // surfaced as exception, no recursion
                }
            })
            ->index('1', ['title' => 'foo'])
            ->flush();

        $this->assertTrue($threw);
        $this->assertEquals(2, $calls); // original + one retry attempt, no recursion
    }

    public function testOnErrorClearsBatchWhenHandlerReturns()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            return new ArrayResponse(['errors' => true, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))
            ->onError(function ($response, $body, $newbulk) {
                // accept and drop
            })
            ->index('1', ['title' => 'foo']);
        $bulk->flush();

        $this->assertEquals(1, $callCount);
        $this->assertEquals([], $bulk->flush()); // batch consumed by handler
    }

    public function testOnErrorPreservesBatchWhenHandlerThrows()
    {
        $callCount = 0;
        $client = $this->createMock(TestClient::class);
        $client->method('bulk')->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            return new ArrayResponse(['errors' => true, 'items' => []]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $bulk = (new Bulk($index))
            ->onError(function ($response, $body, $newbulk) {
                throw new \RuntimeException('handler aborted');
            })
            ->index('1', ['title' => 'foo']);

        try {
            $bulk->flush();
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertSame('handler aborted', $e->getMessage());
        }

        try {
            $bulk->flush(); // batch preserved → re-sent
        } catch (\RuntimeException $e) {
            // still failing, still preserved
        }
        $this->assertEquals(2, $callCount);
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
            ->flush();

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

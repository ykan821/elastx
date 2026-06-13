<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Doc;
use ElasticKit\Index\Index;

class DocTest extends TestCase
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
            public function __construct($name = 'products')
            {
                $this->name = $name;
            }
        };
    }

    public function testId()
    {
        $index = $this->createIndex('products');
        $doc = $index->doc('abc123');
        $this->assertEquals('abc123', $doc->id());
    }

    public function testGet()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('get')
            ->with(['index' => 'products', 'id' => '1'])
            ->willReturn(new ArrayResponse([
                '_index' => 'products',
                '_id' => '1',
                '_source' => ['title' => 'foo'],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->doc('1')->get();

        $this->assertEquals('1', $result['_id']);
        $this->assertEquals(['title' => 'foo'], $result['_source']);
    }

    public function testSource()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('getSource')
            ->with(['index' => 'products', 'id' => '1'])
            ->willReturn(new ArrayResponse(['title' => 'foo']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $source = $index->doc('1')->source();

        $this->assertEquals(['title' => 'foo'], $source);
    }

    public function testExistsReturnsTrue()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('exists')->with(['index' => 'products', 'id' => '1'])->willReturn(new BoolResponse(true));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertTrue($index->doc('1')->exists());
    }

    public function testExistsReturnsFalse()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('exists')->with(['index' => 'products', 'id' => '999'])->willReturn(new BoolResponse(false));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertFalse($index->doc('999')->exists());
    }

    public function testUpdate()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('update')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => [
                    'doc' => ['title' => 'updated'],
                    'doc_as_upsert' => false,
                ],
            ])
            ->willReturn(new ArrayResponse(['result' => 'updated']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->doc('1')->update(['title' => 'updated']);

        $this->assertEquals('updated', $result['result']);
    }

    public function testUpdateWithoutUpsert()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('update')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => [
                    'doc' => ['title' => 'updated'],
                    'doc_as_upsert' => false,
                ],
            ])
            ->willReturn(new ArrayResponse(['result' => 'updated']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->update(['title' => 'updated'], false);
    }

    public function testUpdateWithRetryOnConflict()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('update')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => [
                    'doc' => ['title' => 'updated'],
                    'doc_as_upsert' => false,
                ],
                'retry_on_conflict' => 3,
            ])
            ->willReturn(new ArrayResponse(['result' => 'updated']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->retryOnConflict(3)->update(['title' => 'updated']);
    }

    public function testUpdateWithRefresh()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('update')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => [
                    'doc' => ['title' => 'updated'],
                    'doc_as_upsert' => false,
                ],
                'refresh' => 'wait_for',
            ])
            ->willReturn(new ArrayResponse(['result' => 'updated']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->refresh('wait_for')->update(['title' => 'updated']);
    }

    public function testUpdateWithAllOptions()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('update')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => [
                    'doc' => ['title' => 'updated'],
                    'doc_as_upsert' => false,
                ],
                'retry_on_conflict' => 5,
                'refresh' => 'true',
            ])
            ->willReturn(new ArrayResponse(['result' => 'updated']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->retryOnConflict(5)->refresh('true')->update(['title' => 'updated'], false);
    }

    public function testUpdateOptionsResetAfterCall()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->exactly(2))
            ->method('update')
            ->willReturnMap([
                [
                    [
                        'index' => 'products',
                        'id' => '1',
                        'body' => ['doc' => ['title' => 'first'], 'doc_as_upsert' => false],
                        'retry_on_conflict' => 3,
                        'refresh' => 'wait_for',
                    ],
                    new ArrayResponse(['result' => 'updated']),
                ],
                [
                    [
                        'index' => 'products',
                        'id' => '1',
                        'body' => ['doc' => ['title' => 'second'], 'doc_as_upsert' => false],
                    ],
                    new ArrayResponse(['result' => 'updated']),
                ],
            ]);
        Index::setClient($client);

        $index = $this->createIndex('products');
        $doc = $index->doc('1');
        $doc->retryOnConflict(3)->refresh('wait_for')->update(['title' => 'first']);
        $doc->update(['title' => 'second']);
    }

    public function testIndex()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('index')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => ['title' => 'foo'],
            ])
            ->willReturn(new ArrayResponse(['result' => 'created']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->doc('1')->index(['title' => 'foo']);

        $this->assertEquals('created', $result['result']);
    }

    public function testIndexWithRefresh()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('index')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => ['title' => 'foo'],
                'refresh' => 'wait_for',
            ])
            ->willReturn(new ArrayResponse(['result' => 'created']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->refresh('wait_for')->index(['title' => 'foo']);
    }

    public function testCreate()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('index')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => ['title' => 'foo'],
                'op_type' => 'create',
            ])
            ->willReturn(new ArrayResponse(['result' => 'created']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->doc('1')->create(['title' => 'foo']);

        $this->assertEquals('created', $result['result']);
    }

    public function testCreateWithRefresh()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('index')
            ->with([
                'index' => 'products',
                'id' => '1',
                'body' => ['title' => 'foo'],
                'op_type' => 'create',
                'refresh' => 'true',
            ])
            ->willReturn(new ArrayResponse(['result' => 'created']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->refresh('true')->create(['title' => 'foo']);
    }

    public function testDelete()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('delete')
            ->with(['index' => 'products', 'id' => '1'])
            ->willReturn(new ArrayResponse(['result' => 'deleted']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->doc('1')->delete();

        $this->assertEquals('deleted', $result['result']);
    }

    public function testDeleteWithRefresh()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('delete')
            ->with(['index' => 'products', 'id' => '1', 'refresh' => 'wait_for'])
            ->willReturn(new ArrayResponse(['result' => 'deleted']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $index->doc('1')->refresh('wait_for')->delete();
    }

    public function testIndexAutoGeneratesIdWhenEmpty()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('index')
            ->with(['index' => 'products', 'body' => ['title' => 'foo']])
            ->willReturn(new ArrayResponse(['result' => 'created', '_id' => 'auto']));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->newDoc(null)->index(['title' => 'foo']);

        $this->assertEquals('created', $result['result']);
    }

    public function testUpdateRequiresExplicitId()
    {
        $index = $this->createIndex('products');

        $this->expectException(\RuntimeException::class);
        $index->newDoc(null)->update(['title' => 'foo']);
    }
}

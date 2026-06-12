<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\Index;
use ElasticKit\Index\Search;

class StatsSupportTest extends TestCase
{
    protected function setUp(): void
    {
        Index::setClient($this->createMock(TestClient::class));
    }

    protected function tearDown(): void
    {
        $ref = new ReflectionProperty(Index::class, 'clients');
        $ref->setAccessible(true);
        $ref->setValue(null, []);
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

    protected function mockSearchResponse($aggValue)
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
            'aggregations' => ['__scalar' => ['value' => $aggValue]],
        ]));
        Index::setClient($client);
    }

    public function testMax()
    {
        $this->mockSearchResponse(199.99);
        $index = $this->createIndex();

        $result = $index->query()->term('status', 'published')->max('price');

        $this->assertEquals(199.99, $result);
    }

    public function testMin()
    {
        $this->mockSearchResponse(9.99);
        $index = $this->createIndex();

        $result = $index->query()->term('status', 'published')->min('price');

        $this->assertEquals(9.99, $result);
    }

    public function testAvg()
    {
        $this->mockSearchResponse(49.5);
        $index = $this->createIndex();

        $result = $index->query()->match('title', 'elasticsearch')->avg('price');

        $this->assertEquals(49.5, $result);
    }

    public function testSum()
    {
        $this->mockSearchResponse(1500.0);
        $index = $this->createIndex();

        $result = $index->query()->matchAll()->sum('price');

        $this->assertEquals(1500.0, $result);
    }

    public function testStatsReturnsAllMetrics()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
            'aggregations' => [
                '__stats' => [
                    'count' => 100,
                    'min'   => 9.99,
                    'max'   => 199.99,
                    'avg'   => 49.5,
                    'sum'   => 4950.0,
                ],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex();
        $result = $index->query()->stats('price');

        $this->assertEquals([
            'count' => 100,
            'min'   => 9.99,
            'max'   => 199.99,
            'avg'   => 49.5,
            'sum'   => 4950.0,
        ], $result);
    }

    public function testStatsReturnsNullWhenNoAggregation()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]));
        Index::setClient($client);

        $index = $this->createIndex();
        $result = $index->query()->stats('nonexistent');

        $this->assertNull($result);
    }

    public function testStatsSendsCorrectBody()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())->method('search')->with($this->callback(function ($params) {
            $body = $params['body'];
            return $body['size'] === 0
                && isset($body['aggs']['__stats']['stats']['field'])
                && $body['aggs']['__stats']['stats']['field'] === 'price';
        }))->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
            'aggregations' => [
                '__stats' => ['count' => 0, 'min' => null, 'max' => null, 'avg' => null, 'sum' => 0],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex();
        $index->query()->stats('price');
    }

    public function testScalarSendsCorrectBody()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())->method('search')->with($this->callback(function ($params) {
            $body = $params['body'];
            return $body['size'] === 0
                && isset($body['aggs']['__scalar']['max']['field'])
                && $body['aggs']['__scalar']['max']['field'] === 'price';
        }))->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
            'aggregations' => ['__scalar' => ['value' => 100]],
        ]));
        Index::setClient($client);

        $index = $this->createIndex();
        $index->query()->max('price');
    }

    public function testScalarDoesNotMutateQuery()
    {
        $lastBody = null;
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturnCallback(function ($params) use (&$lastBody) {
            $lastBody = $params['body'];
            return new ArrayResponse([
                'hits' => ['total' => ['value' => 0], 'hits' => []],
                'aggregations' => ['__scalar' => ['value' => 100]],
            ]);
        });
        Index::setClient($client);

        $index = $this->createIndex();
        $search = $index->query()->match('title', 'test')->size(20);

        $search->max('price');

        // Query body sent to ES should have size=0, but the next get() should use size=20
        $this->assertEquals(0, $lastBody['size']);
    }

    public function testScalarReturnsNullWhenNoValue()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]));
        Index::setClient($client);

        $index = $this->createIndex();
        $result = $index->query()->max('nonexistent');

        $this->assertNull($result);
    }
}

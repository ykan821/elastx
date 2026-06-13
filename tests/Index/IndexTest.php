<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\DSL\Query;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Index;
use ElasticKit\Index\Pagination;
use ElasticKit\Index\Results;
use ElasticKit\Index\Search;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        Index::setClient($this->createMock(TestClient::class));
    }

    protected function tearDown(): void
    {
        ClientManager::reset();
        Pagination::reset();
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

    public function testSetClientAndGetClient()
    {
        $client = $this->createMock(TestClient::class);
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertSame($client, $index->getClient());
    }

    public function testQueryReturnsSearch()
    {
        $index = $this->createIndex('products');
        $search = $index->query();

        $this->assertInstanceOf(Search::class, $search);
    }

    public function testQueryReturnsNewInstance()
    {
        $index = $this->createIndex('products');
        $search1 = $index->query();
        $search2 = $index->query();

        $this->assertNotSame($search1, $search2);
    }

    public function testSearchDelegatesQueryDSL()
    {
        $index = $this->createIndex('products');
        $search = $index->query();

        $search->match('title', 'elasticsearch');
        $search->size(10);

        $array = $search->toArray();
        $this->assertArrayHasKey('query', $array);
        $this->assertEquals(10, $array['size']);
    }

    public function testGetReturnsResults()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [['_source' => ['title' => 'elasticsearch']]],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()
            ->match('title', 'elasticsearch')
            ->size(10)
            ->get();

        $this->assertInstanceOf(Results::class, $results);
        $this->assertEquals(1, $results->total());
    }

    public function testGetCallsClientWithCorrectParams()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => [
                        'match' => ['title' => 'elasticsearch'],
                    ],
                    'size' => 10,
                ],
            ])
            ->willReturn(new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_source' => ['title' => 'elasticsearch']]],
                ],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()
            ->match('title', 'elasticsearch')
            ->size(10)
            ->get();

        $this->assertEquals(1, $results->total());
    }

    public function testFirstReturnsSource()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [['_source' => ['title' => 'elasticsearch']]],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $doc = $index->query()
            ->match('title', 'elasticsearch')
            ->first();

        $this->assertEquals(['title' => 'elasticsearch'], $doc);
    }

    public function testFirstReturnsNullWhenEmpty()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 0],
                'hits' => [],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $doc = $index->query()->matchAll()->first();

        $this->assertNull($doc);
    }

    public function testFirstSetsSizeOnQuery()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturnCallback(function ($params) {
            $this->assertEquals(1, $params['body']['size']);
            return new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_source' => ['title' => 'test']]],
                ],
            ]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $result = $index->query()->matchAll()->size(100)->first();

        $this->assertEquals(['title' => 'test'], $result);
    }

    public function testCountReturnsTotal()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('count')->willReturn(new ArrayResponse(['count' => 42]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $count = $index->query()
            ->term('status', 'published')
            ->count();

        $this->assertEquals(42, $count);
    }

    public function testCountDoesNotMutateSearchState()
    {
        $searchBody = null;
        $client = $this->createMock(TestClient::class);
        $client->method('count')->willReturn(new ArrayResponse(['count' => 1]));
        $client->method('search')->willReturnCallback(function ($params) use (&$searchBody) {
            $searchBody = $params['body'];
            return new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [],
                ],
            ]);
        });
        Index::setClient($client);

        $index = $this->createIndex('products');
        $search = $index->query()->matchAll()->size(100);

        $search->count();
        $search->get();

        // size should still be 100 in the search body
        $this->assertEquals(100, $searchBody['size']);
    }

    public function testBoolShorthandOnSearch()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    ['_source' => ['mobile' => '13800138000']],
                    ['_source' => ['mobile' => '13900139000']],
                ],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('users');
        $results = $index->query()
            ->bool(['should' => function (Query $q) {
                $q->term('mobile', '13800138000');
                $q->term('id_card', '13800138000');
            }])
            ->get();

        $this->assertEquals(2, $results->total());
    }

    public function testScrollDefaultsTo1000BatchSize()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'size' => 1000,
                ],
                'scroll' => '1m',
            ])
            ->willReturn(new ArrayResponse([
                '_scroll_id' => 'scroll123',
                'hits' => ['total' => ['value' => 0], 'hits' => []],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->scroll(null, '1m');

        $this->assertEquals('scroll123', $results->scrollId());
    }

    public function testScrollRespectsUserSetSize()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'size' => 500,
                ],
                'scroll' => '5m',
            ])
            ->willReturn(new ArrayResponse([
                '_scroll_id' => 'scroll456',
                'hits' => ['total' => ['value' => 0], 'hits' => []],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->size(500)->scroll(null, '5m');

        $this->assertEquals('scroll456', $results->scrollId());
    }

    public function testScrollContinuesWithScrollId()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('scroll')
            ->with([
                'scroll_id' => 'existing_scroll_id',
                'scroll' => '5m',
            ])
            ->willReturn(new ArrayResponse([
                '_scroll_id' => 'new_scroll_id',
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_source' => ['title' => 'continued']]],
                ],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->scroll('existing_scroll_id');

        $this->assertEquals('new_scroll_id', $results->scrollId());
        $this->assertEquals([['title' => 'continued']], $results->docs());
    }

    public function testNextCallsScrollApi()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('scroll')
            ->with([
                'scroll_id' => 'scroll789',
                'scroll' => '5m',
            ])
            ->willReturn(new ArrayResponse([
                '_scroll_id' => 'scroll789_new',
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_source' => ['title' => 'bar']]],
                ],
            ]));
        Index::setClient($client);

        $previousResults = new Results([
            '_scroll_id' => 'scroll789',
            'hits' => ['total' => ['value' => 1], 'hits' => [['_source' => ['title' => 'foo']]]],
        ]);

        $index = $this->createIndex('products');
        $nextResults = $index->query()->next($previousResults);

        $this->assertEquals('scroll789_new', $nextResults->scrollId());
        $this->assertEquals([['title' => 'bar']], $nextResults->docs());
    }

    public function testClearCallsClearScroll()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('clearScroll')
            ->with(['scroll_id' => 'scroll_abc']);
        Index::setClient($client);

        $results = new Results([
            '_scroll_id' => 'scroll_abc',
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]);

        $index = $this->createIndex('products');
        $index->query()->clear($results);
    }

    public function testClearSkipsWhenNoScrollId()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->never())->method('clearScroll');
        Index::setClient($client);

        $results = new Results([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]);

        $index = $this->createIndex('products');
        $index->query()->clear($results);
    }

    public function testCursorYieldsResultsBatches()
    {
        $client = $this->createMock(TestClient::class);

        $client->method('search')->willReturn(new ArrayResponse([
            '_scroll_id' => 'scroll1',
            'hits' => [
                'total' => ['value' => 3],
                'hits' => [
                    ['_id' => '1', '_source' => ['id' => 1]],
                    ['_id' => '2', '_source' => ['id' => 2]],
                ],
            ],
        ]));

        $callCount = 0;
        $client->method('scroll')->willReturnCallback(function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return new ArrayResponse([
                    '_scroll_id' => 'scroll2',
                    'hits' => [
                        'total' => ['value' => 3],
                        'hits' => [['_id' => '3', '_source' => ['id' => 3]]],
                    ],
                ]);
            }
            return new ArrayResponse([
                '_scroll_id' => 'scroll3',
                'hits' => ['total' => ['value' => 3], 'hits' => []],
            ]);
        });

        $client->method('clearScroll');
        Index::setClient($client);

        $index = $this->createIndex('products');
        $batches = [];
        foreach ($index->query()->matchAll()->cursor('1m') as $results) {
            $this->assertInstanceOf(Results::class, $results);
            $batches[] = $results;
        }

        // First batch: 2 docs, second batch: 1 doc, third batch (empty) stops the loop
        $this->assertCount(2, $batches);
        $this->assertEquals(['1', '2'], $batches[0]->ids());
        $this->assertEquals(['3'], $batches[1]->ids());
    }

    public function testNameReturnsIndexName()
    {
        $index = $this->createIndex('orders');
        $this->assertEquals('orders', $index->name());
    }

    public function testPaginateWithExplicitParams()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'from' => 10,
                    'size' => 5,
                ],
            ])
            ->willReturn(new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 50],
                    'hits' => [['_source' => ['title' => 'test']]],
                ],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->paginate(3, 5);

        $this->assertInstanceOf(Results::class, $results);
        $this->assertEquals(50, $results->total());
        $this->assertEquals(3, $results->page());
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(10, $results->lastPage());
    }

    public function testPaginateWithPageResolver()
    {
        Pagination::setPageResolver(function () {
            return [2, 20];
        });

        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'from' => 20,
                    'size' => 20,
                ],
            ])
            ->willReturn(new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 100],
                    'hits' => [],
                ],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->paginate();

        $this->assertInstanceOf(Results::class, $results);
        $this->assertEquals(100, $results->total());
        $this->assertEquals(2, $results->page());
        $this->assertEquals(20, $results->perPage());
        $this->assertEquals(5, $results->lastPage());
    }

    public function testPaginateReturnsResultsWithMetadata()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 30],
                'hits' => [
                    ['_source' => ['title' => 'a']],
                    ['_source' => ['title' => 'b']],
                ],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->paginate(1, 10);

        $this->assertInstanceOf(Results::class, $results);
        $this->assertEquals(30, $results->total());
        $this->assertEquals(1, $results->page());
        $this->assertEquals(10, $results->perPage());
        $this->assertEquals(3, $results->lastPage());
        $this->assertEquals([['title' => 'a'], ['title' => 'b']], $results->items());
        $this->assertFalse($results->isEmpty());
    }

    public function testPaginateWithoutResolversUsesDefaults()
    {
        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'from' => 0,
                    'size' => 15,
                ],
            ])
            ->willReturn(new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 5],
                    'hits' => [],
                ],
            ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->paginate();

        $this->assertInstanceOf(Results::class, $results);
        $this->assertEquals(5, $results->total());
        $this->assertEquals(1, $results->page());
        $this->assertEquals(15, $results->perPage());
        $this->assertEquals(1, $results->lastPage());
        $this->assertTrue($results->isEmpty());
    }

    public function testPaginateUsesIndexPerPage()
    {
        $index = new class('products') extends Index {
            public function __construct($name = 'products')
            {
                $this->name = $name;
                $this->perPage = 25;
            }
        };

        $client = $this->createMock(TestClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with([
                'index' => 'products',
                'body' => [
                    'query' => ['match_all' => (object)[]],
                    'from' => 25,
                    'size' => 25,
                ],
            ])
            ->willReturn(new ArrayResponse([
                'hits' => [
                    'total' => ['value' => 100],
                    'hits' => [],
                ],
            ]));
        Index::setClient($client);

        $results = $index->query()->matchAll()->paginate(2);

        $this->assertEquals(100, $results->total());
        $this->assertEquals(2, $results->page());
        $this->assertEquals(25, $results->perPage());
        $this->assertEquals(4, $results->lastPage());
    }

    public function testToPaginatorReturnsFrameworkPaginator()
    {
        Pagination::setPaginatorResolver(function (Results $results) {
            return [
                'data' => $results->items(),
                'total' => $results->total(),
                'page' => $results->page(),
                'perPage' => $results->perPage(),
                'lastPage' => $results->lastPage(),
            ];
        });

        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => [
                'total' => ['value' => 30],
                'hits' => [['_source' => ['title' => 'test']]],
            ],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $results = $index->query()->matchAll()->paginate(1, 10);
        $paginator = $results->toPaginator();

        $this->assertEquals([
            'data' => [['title' => 'test']],
            'total' => 30,
            'page' => 1,
            'perPage' => 10,
            'lastPage' => 3,
        ], $paginator);
    }

    public function testToPaginatorThrowsWithoutResolver()
    {
        $results = new Results([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]);

        $this->expectException(\RuntimeException::class);
        $results->toPaginator();
    }

    public function testSetNamedClient()
    {
        $defaultClient = $this->createMock(TestClient::class);
        $logClient = $this->createMock(TestClient::class);

        Index::setClient($defaultClient);
        Index::setClient($logClient, 'log');

        $products = $this->createIndex('products');
        $this->assertSame($defaultClient, $products->getClient());

        $logs = new class('logs') extends Index {
            public function __construct($name)
            {
                $this->name = $name;
                $this->connection = 'log';
            }
        };
        $this->assertSame($logClient, $logs->getClient());
    }

    public function testGetClientResolvesByConnection()
    {
        $logClient = $this->createMock(TestClient::class);
        Index::setClient($logClient, 'log');

        $logs = new class('logs') extends Index {
            public function __construct($name)
            {
                $this->name = $name;
                $this->connection = 'log';
            }
        };

        $this->assertSame($logClient, $logs->getClient());
    }

    public function testGetClientThrowsForUnknownConnection()
    {
        $defaultClient = $this->createMock(TestClient::class);
        Index::setClient($defaultClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('nonexistent');

        $index = new class('unknown') extends Index {
            public function __construct($name)
            {
                $this->name = $name;
                $this->connection = 'nonexistent';
            }
        };

        $index->getClient();
    }

    public function testGetClientThrowsWhenNotRegistered()
    {
        ClientManager::reset();

        $this->expectException(\RuntimeException::class);

        $index = $this->createIndex('test');
        $index->getClient();
    }

    public function testSetConnectionAndGetConnection()
    {
        $index = $this->createIndex('products');

        $this->assertEquals('default', $index->getConnection());

        $result = $index->setConnection('secondary');
        $this->assertSame($index, $result);
        $this->assertEquals('secondary', $index->getConnection());
    }

    public function testOnCreatesInstanceWithConnection()
    {
        $defaultClient = $this->createMock(TestClient::class);
        $secondaryClient = $this->createMock(TestClient::class);
        Index::setClient($defaultClient);
        Index::setClient($secondaryClient, 'secondary');

        $index = TestConcreteIndex::on('secondary');

        $this->assertInstanceOf(TestConcreteIndex::class, $index);
        $this->assertEquals('secondary', $index->getConnection());
        $this->assertEquals('test', $index->name());
        $this->assertSame($secondaryClient, $index->getClient());
    }

    public function testNewQueryReturnsSearch()
    {
        $client = $this->createMock(TestClient::class);
        $client->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 1], 'hits' => [['_source' => ['title' => 'test']]]],
        ]));
        Index::setClient($client);

        $index = $this->createIndex('products');
        $this->assertInstanceOf(Search::class, $index->newQuery());

        $query = Query::create()->match('title', 'test');
        $results = $index->newQuery($query)->get();
        $this->assertEquals(1, $results->total());
    }

    public function testNewQueryUsesInstanceConnection()
    {
        $secondaryClient = $this->createMock(TestClient::class);
        $secondaryClient->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 0], 'hits' => []],
        ]));
        Index::setClient($secondaryClient, 'secondary');

        $index = $this->createIndex('products');
        $index->setConnection('secondary');
        $results = $index->newQuery()->matchAll()->get();

        $this->assertEquals(0, $results->total());
    }

    public function testNewDocUsesInstanceConnection()
    {
        $secondaryClient = $this->createMock(TestClient::class);
        $secondaryClient->method('getSource')->willReturn(new ArrayResponse([
            'title' => 'test',
        ]));
        Index::setClient($secondaryClient, 'secondary');

        $index = $this->createIndex('products');
        $index->setConnection('secondary');
        $doc = $index->newDoc(1);

        $this->assertInstanceOf(\ElasticKit\Index\Doc::class, $doc);
        $this->assertEquals(['title' => 'test'], $doc->source());
    }

    public function testQueryDocDelegateToNew()
    {
        $this->assertInstanceOf(Search::class, TestConcreteIndex::query());
        $this->assertInstanceOf(\ElasticKit\Index\Doc::class, TestConcreteIndex::doc(1));
    }

    public function testOnNewQueryChain()
    {
        $secondaryClient = $this->createMock(TestClient::class);
        $secondaryClient->method('search')->willReturn(new ArrayResponse([
            'hits' => ['total' => ['value' => 1], 'hits' => [['_source' => ['title' => 'from_secondary']]]],
        ]));
        Index::setClient($secondaryClient, 'secondary');

        $results = TestConcreteIndex::on('secondary')->newQuery()->matchAll()->get();

        $this->assertEquals(1, $results->total());
        $this->assertEquals([['title' => 'from_secondary']], $results->docs());
    }

    public function testOnNewDocChain()
    {
        $secondaryClient = $this->createMock(TestClient::class);
        $secondaryClient->method('getSource')->willReturn(new ArrayResponse([
            'title' => 'secondary_doc',
        ]));
        Index::setClient($secondaryClient, 'secondary');

        $doc = TestConcreteIndex::on('secondary')->newDoc(42);

        $this->assertEquals(['title' => 'secondary_doc'], $doc->source());
    }
}

class TestConcreteIndex extends Index
{
    protected string $name = 'test';
}

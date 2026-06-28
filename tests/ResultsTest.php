<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\Exception\PaginationTotalUnavailableException;
use ElasticKit\Index\Support\ClientManager;
use ElasticKit\Index\Support\Pagination;
use ElasticKit\Index\Results;

/**
 * Results parsing — pure unit-style assertions on constructed ES response
 * shapes (no ES connection needed). Lives in the integration suite alongside
 * the search tests it relates to.
 */
class ResultsTest extends TestCase
{
    protected function tearDown(): void
    {
        ClientManager::reset();
        Pagination::reset();
    }

    private function makeResponse(array $overrides = []): array
    {
        return array_merge([
            'hits' => [
                'total' => ['value' => 0, 'relation' => 'eq'],
                'hits' => [],
            ],
        ], $overrides);
    }

    public function testTotal()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 42, 'relation' => 'eq'],
                'hits' => [],
            ],
        ]));
        $this->assertEquals(42, $results->total());
    }

    public function testHits()
    {
        $hits = [
            ['_id' => '1', '_source' => ['title' => 'foo']],
            ['_id' => '2', '_source' => ['title' => 'bar']],
        ];
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 2, 'relation' => 'eq'],
                'hits' => $hits,
            ],
        ]));
        $this->assertEquals($hits, $results->hits());
    }

    public function testDocs()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 2, 'relation' => 'eq'],
                'hits' => [
                    ['_id' => '1', '_source' => ['title' => 'foo']],
                    ['_id' => '2', '_source' => ['title' => 'bar']],
                ],
            ],
        ]));
        $this->assertEquals([['title' => 'foo'], ['title' => 'bar']], $results->docs());
    }

    public function testAggregations()
    {
        $aggs = ['price_avg' => ['value' => 100.5]];
        $results = new Results($this->makeResponse(['aggregations' => $aggs]));
        $this->assertEquals($aggs, $results->aggregations());
    }

    public function testAggregationsReturnsNullWhenAbsent()
    {
        $results = new Results($this->makeResponse());
        $this->assertNull($results->aggregations());
    }

    public function testScrollId()
    {
        $results = new Results($this->makeResponse(['_scroll_id' => 'abc123']));
        $this->assertEquals('abc123', $results->scrollId());
    }

    public function testScrollIdReturnsNullWhenAbsent()
    {
        $results = new Results($this->makeResponse());
        $this->assertNull($results->scrollId());
    }

    public function testTotalRelationEq()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 42, 'relation' => 'eq'],
                'hits' => [],
            ],
        ]));
        $this->assertEquals('eq', $results->totalRelation());
    }

    public function testTotalRelationGte()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 10000, 'relation' => 'gte'],
                'hits' => [],
            ],
        ]));
        $this->assertEquals('gte', $results->totalRelation());
    }

    public function testRaw()
    {
        $response = $this->makeResponse(['took' => 5]);
        $results = new Results($response);
        $this->assertEquals($response, $results->raw());
    }

    public function testPaginateSetsMetadata()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 50, 'relation' => 'eq'],
                'hits' => [],
            ],
        ]));
        $results->paginate(3, 10);

        $this->assertEquals(3, $results->page());
        $this->assertEquals(10, $results->perPage());
        $this->assertEquals(5, $results->lastPage());
    }

    public function testLastPageMinimumIsOne()
    {
        $results = new Results($this->makeResponse());
        $results->paginate(1, 15);

        $this->assertEquals(1, $results->lastPage());
    }

    public function testLastPageWithZeroPerPageDoesNotCrash()
    {
        $results = new Results($this->makeResponse([
            'hits' => ['total' => ['value' => 50, 'relation' => 'eq'], 'hits' => []],
        ]));
        $results->paginate(1, 0);

        $this->assertEquals(1, $results->lastPage()); // guarded, no DivisionByZeroError
    }

    public function testItemsReturnsDocs()
    {
        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 2, 'relation' => 'eq'],
                'hits' => [
                    ['_id' => '1', '_source' => ['title' => 'foo']],
                    ['_id' => '2', '_source' => ['title' => 'bar']],
                ],
            ],
        ]));
        $this->assertEquals($results->docs(), $results->items());
    }

    public function testIsEmpty()
    {
        $results = new Results($this->makeResponse());
        $this->assertTrue($results->isEmpty());

        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 1, 'relation' => 'eq'],
                'hits' => [['_source' => ['title' => 'foo']]],
            ],
        ]));
        $this->assertFalse($results->isEmpty());
    }

    public function testToPaginatorCallsResolver()
    {
        Pagination::setPaginatorResolver(function (Results $results) {
            return ['total' => $results->total(), 'page' => $results->page()];
        });

        $results = new Results($this->makeResponse([
            'hits' => [
                'total' => ['value' => 50, 'relation' => 'eq'],
                'hits' => [],
            ],
        ]));
        $results->paginate(2, 10);

        $paginator = $results->toPaginator();
        $this->assertEquals(['total' => 50, 'page' => 2], $paginator);
    }

    public function testToPaginatorThrowsWithoutResolver()
    {
        $results = new Results($this->makeResponse());
        $this->expectException(\RuntimeException::class);
        $results->toPaginator();
    }

    public function testTook()
    {
        $results = new Results($this->makeResponse(['took' => 5]));
        $this->assertEquals(5, $results->took());
    }

    public function testTimedOut()
    {
        $results = new Results($this->makeResponse(['timed_out' => true]));
        $this->assertTrue($results->timedOut());
    }

    public function testTimedOutFalse()
    {
        $results = new Results($this->makeResponse(['timed_out' => false]));
        $this->assertFalse($results->timedOut());
    }

    public function testTotalIsNullWhenOmitted()
    {
        // track_total_hits=false omits hits.total entirely.
        $results = new Results(['hits' => ['hits' => []]]);
        $this->assertNull($results->total());
        $this->assertNull($results->totalRelation());
    }

    public function testLastPageIsNullWhenTotalUnavailable()
    {
        $results = (new Results(['hits' => ['hits' => []]]))->paginate(1, 15);
        $this->assertNull($results->lastPage());
    }

    public function testHasMorePagesUsesTotalWhenKnown()
    {
        $results = (new Results($this->makeResponse([
            'hits' => ['total' => ['value' => 33, 'relation' => 'eq'], 'hits' => []],
        ])))->paginate(1, 15);
        $this->assertTrue($results->hasMorePages()); // page 1 < lastPage 3
    }

    public function testHasMorePagesFullPageHeuristicWhenNoTotal()
    {
        $full = array_fill(0, 15, ['_id' => 'x', '_source' => []]);
        $results = (new Results(['hits' => ['hits' => $full]]))->paginate(1, 15);
        $this->assertTrue($results->hasMorePages()); // full page -> probably more

        $partial = array_fill(0, 10, ['_id' => 'x', '_source' => []]);
        $results = (new Results(['hits' => ['hits' => $partial]]))->paginate(1, 15);
        $this->assertFalse($results->hasMorePages()); // partial page -> last
    }

    public function testToPaginatorThrowsWhenTotalUnavailable()
    {
        $results = (new Results(['hits' => ['hits' => []]]))->paginate(1, 15);
        $this->expectException(PaginationTotalUnavailableException::class);
        $results->toPaginator();
    }
}

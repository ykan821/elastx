<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use Tests\Integration\IntegrationTestCase;

class SearchContractTest extends IntegrationTestCase
{
    public function testGet(): void
    {
        $results = $this->makeIndex()->newQuery()->matchAll()->get();
        $this->assertSame(3, $results->total());
        $this->assertCount(3, $results->hits());
    }

    public function testFirst(): void
    {
        $doc = $this->makeIndex()->newQuery()->match('content', 'elasticsearch')->first();
        $this->assertIsArray($doc);
        $this->assertContains($doc['title'], ['Elasticsearch Guide', 'PHP Development']);
    }

    public function testFirstEmpty(): void
    {
        $doc = $this->makeIndex()->newQuery()->term('status', 'nonexistent')->first();
        $this->assertNull($doc);
    }

    public function testCount(): void
    {
        $count = $this->makeIndex()->newQuery()->term('status', 'published')->count();
        $this->assertSame(2, $count);
    }

    public function testPaginate(): void
    {
        $results = $this->makeIndex()->newQuery()->matchAll()->paginate(1, 2);
        $this->assertSame(3, $results->total());
        $this->assertSame(1, $results->page());
        $this->assertSame(2, $results->perPage());
        $this->assertSame(2, $results->lastPage());
        $this->assertCount(2, $results->items());
    }

    public function testPaginateLastPage(): void
    {
        $results = $this->makeIndex()->newQuery()->matchAll()->paginate(2, 2);
        $this->assertCount(1, $results->items());
    }

    public function testScroll(): void
    {
        $results = $this->makeIndex()->newQuery()->matchAll()->scroll(null, '1m');
        $this->assertNotEmpty($results->scrollId());
        $this->assertGreaterThanOrEqual(1, count($results->hits()));
    }

    public function testChunk(): void
    {
        $count = 0;
        foreach ($this->makeIndex()->newQuery()->matchAll()->chunk('1m') as $results) {
            $count += count($results->hits());
        }
        $this->assertSame(3, $count);
    }

    public function testCursor(): void
    {
        $count = 0;
        foreach ($this->makeIndex()->newQuery()->matchAll()->cursor('1m') as $hit) {
            $count++;
        }
        $this->assertSame(3, $count);
    }
}

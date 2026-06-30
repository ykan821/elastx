<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use Tests\Integration\IntegrationTestCase;

class DocContractTest extends IntegrationTestCase
{
    public function testIndexAndGet(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('7')->index(['title' => 'new doc']);
        $this->refreshIndex();
        $doc = $index->newDoc('7')->get();
        $this->assertSame('7', $doc['_id']);
        $this->assertSame(['title' => 'new doc'], $doc['_source']);
    }

    public function testSource(): void
    {
        $source = $this->makeIndex()->newDoc('1')->source();
        $this->assertSame('Elasticsearch Guide', $source['title']);
    }

    public function testExists(): void
    {
        $index = $this->makeIndex();
        $this->assertTrue($index->newDoc('1')->exists());
        $this->assertFalse($index->newDoc('999')->exists());
    }

    public function testUpdate(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('1')->update(['price' => 99]);
        $this->refreshIndex();
        $this->assertEquals(99, $index->newDoc('1')->source()['price']);
    }

    public function testUpsert(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('999')->update(['title' => 'upserted'], true);
        $this->refreshIndex();
        $this->assertTrue($index->newDoc('999')->exists());
    }

    public function testCreateConflict(): void
    {
        $index = $this->makeIndex();
        try {
            $index->newDoc('1')->create(['title' => 'duplicate']);
            $this->fail('Expected version conflict for create on existing id');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('version_conflict', $e->getMessage());
        }
    }

    public function testDelete(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('1')->delete();
        $this->refreshIndex();
        $this->assertFalse($index->newDoc('1')->exists());
    }

    public function testAutoId(): void
    {
        $result = $this->makeIndex()->newDoc(null)->index(['title' => 'auto']);
        $this->refreshIndex();
        $this->assertNotEmpty($result['_id'] ?? null);
    }

    public function testUpdateRequiresId(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->makeIndex()->newDoc(null)->update(['title' => 'x']);
    }

    public function testCreateSuccess(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('40')->create(['title' => 'fresh']);
        $this->refreshIndex();
        $this->assertSame('fresh', $index->newDoc('40')->source()['title']);
    }

    public function testSave(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('41')->save(['title' => 'saved']);
        $this->refreshIndex();
        $this->assertSame('saved', $index->newDoc('41')->source()['title']);
    }

    public function testRetryOnConflictChain(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('1')->retryOnConflict(3)->update(['price' => 77]);
        $this->refreshIndex();
        $this->assertEquals(77, $index->newDoc('1')->source()['price']);
    }

    public function testRefreshOption(): void
    {
        $index = $this->makeIndex();
        $index->newDoc('42')->refresh('wait_for')->index(['title' => 'refreshed']);
        // refresh=wait_for makes it searchable immediately
        $this->assertSame('refreshed', $index->newDoc('42')->source()['title']);
    }

    public function testRefreshAcceptsBool(): void
    {
        $index = $this->makeIndex();
        // bool refresh (true/false) is valid per ES alongside 'wait_for';
        // verify the widened string|bool type is accepted and the write lands.
        $index->newDoc('43')->refresh(true)->index(['title' => 'bool-refresh']);
        $this->assertSame('bool-refresh', $index->newDoc('43')->source()['title']);
    }
}

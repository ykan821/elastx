<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use ElasticKit\Index\Bulk;
use Tests\Integration\IntegrationTestCase;

class BulkContractTest extends IntegrationTestCase
{
    public function testIndexAndFlush(): void
    {
        $index = $this->makeIndex();
        (new Bulk($index))->index('10', ['title' => 'bulk doc'])->flush();
        $this->refreshIndex();
        $this->assertTrue($index->newDoc('10')->exists());
    }

    public function testCreate(): void
    {
        $index = $this->makeIndex();
        (new Bulk($index))->create('11', ['title' => 'created'])->flush();
        $this->refreshIndex();
        $this->assertTrue($index->newDoc('11')->exists());
    }

    public function testUpdate(): void
    {
        $index = $this->makeIndex();
        (new Bulk($index))->update('1', ['price' => 88])->flush();
        $this->refreshIndex();
        $this->assertEquals(88, $index->newDoc('1')->source()['price']);
    }

    public function testDelete(): void
    {
        $index = $this->makeIndex();
        (new Bulk($index))->delete('1')->flush();
        $this->refreshIndex();
        $this->assertFalse($index->newDoc('1')->exists());
    }

    public function testBatchSizeAutoFlush(): void
    {
        $index = $this->makeIndex();
        $bulk = (new Bulk($index))->batchSize(2);
        $bulk->index('20', ['title' => 'a']);
        $bulk->index('21', ['title' => 'b']); // auto-flush at 2
        $bulk->index('22', ['title' => 'c']);
        $bulk->flush(); // tail
        $this->refreshIndex();
        $this->assertTrue($index->newDoc('20')->exists());
        $this->assertTrue($index->newDoc('21')->exists());
        $this->assertTrue($index->newDoc('22')->exists());
    }

    public function testOnErrorReceivesFailures(): void
    {
        // create on the already-seeded id '1' triggers a bulk error -> onError
        $index = $this->makeIndex();
        $received = null;
        (new Bulk($index))
            ->onError(function ($response) use (&$received) {
                $received = $response;
            })
            ->create('1', ['title' => 'dup'])
            ->flush();
        $this->assertTrue($received['errors'] ?? false);
    }
}

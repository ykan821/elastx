<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use ElasticKit\Index\Index;
use ElasticKit\Index\Rebuild;
use Tests\Integration\IntegrationTestCase;

class RebuildContractTest extends IntegrationTestCase
{
    public function testRunCreatesAlias(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = new class($alias) extends Index {
            public function __construct(string $alias)
            {
                $this->name = $alias;
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
                yield 2 => ['title' => 'B'];
            }
        };

        $result = (new Rebuild($index))->run();
        $this->assertNotEmpty($result['newIndex']);
        $this->assertNull($result['oldIndex']);

        // alias now resolves to the backing index with the 2 imported docs
        $index->getClient()->indices()->refresh(['index' => $result['newIndex']]);
        $this->assertSame(2, $index->newQuery()->matchAll()->count());
    }

    public function testRunSwapsAlias(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = new class($alias) extends Index {
            public function __construct(string $alias)
            {
                $this->name = $alias;
            }

            public function rebuildName(): string
            {
                return $this->name . '_' . bin2hex(random_bytes(2));
            }

            public function source(array $context = []): iterable
            {
                yield 1 => ['title' => 'A'];
            }
        };

        $first = (new Rebuild($index))->run();
        $this->assertNull($first['oldIndex']);

        $second = (new Rebuild($index))->run();
        $this->assertSame($first['newIndex'], $second['oldIndex']);
    }

    public function testRunRejectsRealIndex(): void
    {
        // $this->indexName is a real index created by setUp, not an alias
        $name = $this->indexName;
        $index = new class($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
            }

            public function source(array $context = []): iterable
            {
                return [];
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is a real index');
        (new Rebuild($index))->allowEmpty()->run();
    }
}

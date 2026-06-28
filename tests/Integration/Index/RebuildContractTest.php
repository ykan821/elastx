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
        $index = new class ($alias) extends Index {
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
        $index = new class ($alias) extends Index {
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
        $index = new class ($name) extends Index {
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

    private function rebuildIndex(string $alias, bool $empty = false): Index
    {
        return new class ($alias, $empty) extends Index {
            private bool $empty;

            public function __construct(string $alias, bool $empty)
            {
                $this->name = $alias;
                $this->empty = $empty;
            }

            public function rebuildName(): string
            {
                return $this->name . '_' . bin2hex(random_bytes(2));
            }

            public function source(array $context = []): iterable
            {
                if ($this->empty) {
                    return [];
                }
                yield 1 => ['title' => 'A'];
            }
        };
    }

    public function testRollback(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias);
        $first = (new Rebuild($index))->run();
        $second = (new Rebuild($index))->run();
        $rolledBackFrom = (new Rebuild($index))->rollback($first['newIndex']);
        $this->assertSame($second['newIndex'], $rolledBackFrom);
    }

    public function testClean(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias);
        $result = (new Rebuild($index))->run();
        (new Rebuild($index))->clean($result['newIndex']);
        $this->assertFalse($index->getClient()->indices()->exists(['index' => $result['newIndex']])->asBool());
    }

    public function testForceUnlockIsIdempotent(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias);
        // no lock held yet -> forceUnlock tolerates the 404
        (new Rebuild($index))->forceUnlock();
        $this->assertFalse((new Rebuild($index))->isLocked());
    }

    public function testIsLockedFalseAfterRun(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias);
        (new Rebuild($index))->run();
        $this->assertFalse((new Rebuild($index))->isLocked());
    }

    public function testEmptySourceThrowsWithoutAllowEmpty(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias, empty: true);
        $this->expectException(\RuntimeException::class);
        (new Rebuild($index))->run();
    }

    public function testAllowEmpty(): void
    {
        $alias = 'ek_rebuild_' . bin2hex(random_bytes(4));
        $index = $this->rebuildIndex($alias, empty: true);
        $result = (new Rebuild($index))->allowEmpty()->run();
        $this->assertNotEmpty($result['newIndex']);
        $this->assertNull($result['oldIndex']);
    }
}

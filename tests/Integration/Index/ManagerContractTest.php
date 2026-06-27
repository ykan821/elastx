<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use ElasticKit\Index\Index;
use ElasticKit\Index\Manager;
use Tests\Integration\IntegrationTestCase;

class ManagerContractTest extends IntegrationTestCase
{
    public function testExists(): void
    {
        $this->assertTrue((new Manager($this->makeIndex()))->exists());
    }

    public function testPutMapping(): void
    {
        $name = $this->indexName;
        $index = new class($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
                $this->mappings = ['properties' => ['extra' => ['type' => 'keyword']]];
            }
        };
        $manager = new Manager($index);
        $manager->putMapping();
        $mapping = $manager->getMapping();
        $this->assertArrayHasKey('extra', $mapping[$name]['mappings']['properties']);
    }

    public function testAddAndRemoveAlias(): void
    {
        $manager = new Manager($this->makeIndex());
        $manager->addAlias('ek_alias_test');
        $aliases = $manager->getAliases();
        $this->assertArrayHasKey('ek_alias_test', $aliases[$this->indexName]['aliases']);
        $manager->removeAlias('ek_alias_test');
        $aliases = $manager->getAliases();
        $this->assertArrayNotHasKey('ek_alias_test', $aliases[$this->indexName]['aliases']);
    }

    public function testRefresh(): void
    {
        // refresh must run without error on a real index
        (new Manager($this->makeIndex()))->refresh();
        $this->assertTrue((new Manager($this->makeIndex()))->exists());
    }

    public function testDelete(): void
    {
        $name = 'ek_mgr_' . bin2hex(random_bytes(4));
        $index = new class($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
            }
        };
        $manager = new Manager($index);
        $manager->create();
        $this->assertTrue($manager->exists());
        $manager->delete();
        $this->assertFalse($manager->exists());
    }

    public function testCreate(): void
    {
        $name = 'ek_mgr_' . bin2hex(random_bytes(4));
        $index = new class($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
                $this->mappings = ['properties' => ['title' => ['type' => 'text']]];
            }
        };
        $manager = new Manager($index);
        $this->assertFalse($manager->exists());
        $manager->create();
        $this->assertTrue($manager->exists());
    }

    public function testGet(): void
    {
        $info = (new Manager($this->makeIndex()))->get();
        $this->assertArrayHasKey($this->indexName, $info);
        $this->assertArrayHasKey('mappings', $info[$this->indexName]);
        $this->assertArrayHasKey('settings', $info[$this->indexName]);
    }

    public function testPutSettings(): void
    {
        $manager = new Manager($this->makeIndex());
        $manager->putSettings(['index' => ['number_of_replicas' => 0]]);
        $settings = $manager->getSettings();
        $this->assertSame('0', $settings[$this->indexName]['settings']['index']['number_of_replicas'] ?? null);
    }

    public function testCloseAndOpen(): void
    {
        $name = 'ek_mgr_' . bin2hex(random_bytes(4));
        $index = new class($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
            }
        };
        $manager = new Manager($index);
        $manager->create();
        $manager->close();
        $manager->open();
        $this->assertTrue($manager->exists());
    }
}

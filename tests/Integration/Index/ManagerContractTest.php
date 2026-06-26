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
        $manager = new Manager($this->makeIndex());
        $this->assertTrue($manager->exists());
        $manager->delete();
        $this->assertFalse($manager->exists());
    }
}

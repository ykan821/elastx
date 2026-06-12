<?php

use PHPUnit\Framework\TestCase;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Index;
use ElasticKit\Index\Manager;

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        Index::setClient($this->createMock(TestClient::class));
    }

    protected function tearDown(): void
    {
        ClientManager::reset();
    }

    protected function createIndex($name = 'products', $mappings = [], $settings = [])
    {
        return new class($name, $mappings, $settings) extends Index {
            public function __construct($name, $mappings, $settings)
            {
                $this->name = $name;
                $this->mappings = $mappings;
                $this->settings = $settings;
            }
        };
    }

    protected function mockIndices($method, $with, $return)
    {
        $wrapped = is_bool($return) ? new BoolResponse($return) : new ArrayResponse($return);

        $indices = $this->createMock(TestIndices::class);
        $indices->method('existsAlias')->willReturn(new BoolResponse(false));
        $indices->expects($this->once())->method($method)->with($with)->willReturn($wrapped);

        $client = $this->createMock(TestClient::class);
        $client->method('indices')->willReturn($indices);
        Index::setClient($client);
    }

    public function testCreate()
    {
        $this->mockIndices('create', [
            'index' => 'products',
            'body' => [
                'settings' => ['number_of_shards' => 3],
                'mappings' => ['properties' => ['title' => ['type' => 'text']]],
            ],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products', ['properties' => ['title' => ['type' => 'text']]], ['number_of_shards' => 3]);
        $result = (new Manager($index))->create();

        $this->assertTrue($result['acknowledged']);
    }

    public function testCreateWithoutBody()
    {
        $this->mockIndices('create', [
            'index' => 'products',
            'body' => [
                'mappings' => new \stdClass(),
                'settings' => new \stdClass(),
            ],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        $result = (new Manager($index))->create();

        $this->assertTrue($result['acknowledged']);
    }

    public function testDelete()
    {
        $this->mockIndices('delete', ['index' => 'products'], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        $result = (new Manager($index))->delete();

        $this->assertTrue($result['acknowledged']);
    }

    public function testExistsReturnsTrue()
    {
        $this->mockIndices('exists', ['index' => 'products'], true);

        $index = $this->createIndex('products');
        $this->assertTrue((new Manager($index))->exists());
    }

    public function testExistsReturnsFalse()
    {
        $this->mockIndices('exists', ['index' => 'products'], false);

        $index = $this->createIndex('products');
        $this->assertFalse((new Manager($index))->exists());
    }

    public function testGet()
    {
        $return = ['products' => ['aliases' => [], 'mappings' => [], 'settings' => []]];
        $this->mockIndices('get', ['index' => 'products'], $return);

        $index = $this->createIndex('products');
        $this->assertEquals($return, (new Manager($index))->get());
    }

    public function testPutMapping()
    {
        $this->mockIndices('putMapping', [
            'index' => 'products',
            'body' => ['properties' => ['title' => ['type' => 'text']]],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products', ['properties' => ['title' => ['type' => 'text']]]);
        (new Manager($index))->putMapping();
    }

    public function testGetMapping()
    {
        $return = ['products' => ['mappings' => ['properties' => []]]];
        $this->mockIndices('getMapping', ['index' => 'products'], $return);

        $index = $this->createIndex('products');
        $this->assertEquals($return, (new Manager($index))->getMapping());
    }

    public function testPutSettings()
    {
        $this->mockIndices('putSettings', [
            'index' => 'products',
            'body' => ['index' => ['number_of_replicas' => 2]],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->putSettings(['index' => ['number_of_replicas' => 2]]);
    }

    public function testGetSettings()
    {
        $return = ['products' => ['settings' => ['index' => ['number_of_replicas' => '1']]]];
        $this->mockIndices('getSettings', ['index' => 'products'], $return);

        $index = $this->createIndex('products');
        $this->assertEquals($return, (new Manager($index))->getSettings());
    }

    public function testRefresh()
    {
        $this->mockIndices('refresh', ['index' => 'products'], ['_shards' => ['total' => 1]]);

        $index = $this->createIndex('products');
        (new Manager($index))->refresh();
    }

    public function testForceMerge()
    {
        $this->mockIndices('forcemerge', ['index' => 'products'], ['_shards' => ['total' => 1]]);

        $index = $this->createIndex('products');
        (new Manager($index))->forceMerge();
    }

    public function testForceMergeWithOptions()
    {
        $this->mockIndices('forcemerge', [
            'index' => 'products',
            'max_num_segments' => 1,
        ], ['_shards' => ['total' => 1]]);

        $index = $this->createIndex('products');
        (new Manager($index))->forceMerge(['max_num_segments' => 1]);
    }

    public function testClose()
    {
        $this->mockIndices('close', ['index' => 'products'], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->close();
    }

    public function testOpen()
    {
        $this->mockIndices('open', ['index' => 'products'], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->open();
    }

    public function testAddAlias()
    {
        $this->mockIndices('putAlias', [
            'index' => 'products',
            'name' => 'products_active',
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->addAlias('products_active');
    }

    public function testAddAliasWithOptions()
    {
        $this->mockIndices('putAlias', [
            'index' => 'products',
            'name' => 'products_active',
            'body' => ['is_write_index' => true],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->addAlias('products_active', ['is_write_index' => true]);
    }

    public function testRemoveAlias()
    {
        $this->mockIndices('deleteAlias', [
            'index' => 'products',
            'name' => 'products_active',
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->removeAlias('products_active');
    }

    public function testSwapAlias()
    {
        $this->mockIndices('updateAliases', [
            'body' => [
                'actions' => [
                    ['remove' => ['index' => 'products_v1', 'alias' => 'products_active']],
                    ['add' => ['index' => 'products', 'alias' => 'products_active']],
                ],
            ],
        ], ['acknowledged' => true]);

        $index = $this->createIndex('products');
        (new Manager($index))->swapAlias('products_active', 'products_v1');
    }

    public function testGetAliases()
    {
        $return = ['products' => ['aliases' => ['products_active' => []]]];
        $this->mockIndices('getAlias', ['index' => 'products'], $return);

        $index = $this->createIndex('products');
        $this->assertEquals($return, (new Manager($index))->getAliases());
    }
}

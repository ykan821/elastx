<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Shape\Shape;
use Tests\Integration\IntegrationTestCase;

/**
 * Shape query contracts against a real Elasticsearch.
 *
 * The cartesian `shape` query needs a `shape`-typed field, which the shared
 * index does not have (its `shape` field is geo_shape), so each test builds a
 * dedicated index with two disjoint envelopes.
 */
class ShapeContractTest extends IntegrationTestCase
{
    private ?string $shapeIndex = null;

    protected function tearDown(): void
    {
        if ($this->shapeIndex !== null && static::$esClient !== null) {
            try {
                static::$esClient->indices()->delete(['index' => $this->shapeIndex]);
            } catch (\Throwable $e) {
                // best-effort cleanup
            }
            $this->shapeIndex = null;
        }
        parent::tearDown();
    }

    private function withShapeIndex(): string
    {
        $client = static::$esClient;
        $index = 'ek_shape_' . bin2hex(random_bytes(4));
        $this->shapeIndex = $index;

        $client->indices()->create([
            'index' => $index,
            'body' => ['mappings' => ['properties' => ['geom' => ['type' => 'shape']]]],
        ]);
        $client->index(['index' => $index, 'id' => '1', 'body' => ['geom' => ['type' => 'envelope', 'coordinates' => [[0, 10], [10, 0]]]]]);
        $client->index(['index' => $index, 'id' => '2', 'body' => ['geom' => ['type' => 'envelope', 'coordinates' => [[100, 110], [110, 100]]]]]);
        $client->indices()->refresh(['index' => $index]);

        return $index;
    }

    public function testShapeIntersects(): void
    {
        $index = $this->withShapeIndex();
        // query envelope overlaps doc 1's [[0,0],[10,10]] only
        $q = (new Query())->shape('geom', function (Shape $shape) {
            $shape->shape(['type' => 'envelope', 'coordinates' => [[5, 15], [15, 5]]])
                ->relation('intersects');
        });
        $total = static::$esClient->search(['index' => $index, 'body' => $q->toArray()])->asArray()['hits']['total']['value'] ?? 0;
        $this->assertSame(1, $total);
    }

    public function testShapeDisjoint(): void
    {
        $index = $this->withShapeIndex();
        // relation DISJOINT inverts: doc 2 (far) matches instead of doc 1
        $q = (new Query())->shape('geom', function (Shape $shape) {
            $shape->shape(['type' => 'envelope', 'coordinates' => [[5, 15], [15, 5]]])
                ->relation('disjoint');
        });
        $total = static::$esClient->search(['index' => $index, 'body' => $q->toArray()])->asArray()['hits']['total']['value'] ?? 0;
        $this->assertSame(1, $total);
    }
}

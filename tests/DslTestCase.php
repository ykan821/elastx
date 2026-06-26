<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ElasticKit\DSL\Query;

/**
 * Base test case for DSL tests: asserts the built JSON structure.
 *
 * ES integration validation (connecting to ES, sending the query) lives in the
 * integration test layer, which reuses the index/seed helpers below.
 */
abstract class DslTestCase extends TestCase
{
    /**
     * @var \Elastic\Elasticsearch\ClientInterface|null
     */
    protected static $esClient;

    /**
     * Assert Query produces the expected JSON structure.
     *
     * @param string $expectedJson
     * @param Query $query
     */
    protected function assertQuery(string $expectedJson, Query $query)
    {
        $this->assertJsonStringEqualsJsonString($expectedJson, $query->toJson(), 'JSON mismatch');
    }

    /**
     * Create the test index with full mapping.
     */
    protected static function createIndex(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
    {
        $client->indices()->create([
            'index' => $index,
            'body'  => [
                'mappings' => [
                    'properties' => [
                        'title'      => ['type' => 'text', 'fielddata' => true],
                        'content'    => ['type' => 'text'],
                        'status'     => ['type' => 'keyword'],
                        'price'      => ['type' => 'float'],
                        'score'      => ['type' => 'float'],
                        'rank'       => ['type' => 'float'],
                        'popularity' => ['type' => 'float'],
                        'tags'       => ['type' => 'keyword'],
                        'author'     => ['type' => 'keyword'],
                        'color'      => ['type' => 'keyword'],
                        'category'   => ['type' => 'keyword'],
                        'created'    => ['type' => 'date'],
                        'location'   => ['type' => 'geo_point'],
                        'shape'      => [
                            'type' => 'geo_shape',
                        ],
                        'comments' => [
                            'type' => 'nested',
                            'properties' => [
                                'author' => ['type' => 'keyword'],
                                'content' => ['type' => 'text'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Seed test documents.
     */
    protected static function seedData(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
    {
        $docs = [
            [
                'id' => '1',
                'body' => [
                    'title'    => 'Elasticsearch Guide',
                    'content'  => 'A comprehensive guide to elasticsearch and database design',
                    'status'   => 'published',
                    'price'    => 25.0,
                    'score'    => 8.5,
                    'rank'     => 3.2,
                    'popularity' => 100,
                    'tags'     => ['search', 'guide'],
                    'author'   => 'alice',
                    'color'    => 'red',
                    'category' => 'search',
                    'created'  => '2024-01-15',
                    'location' => ['lat' => 40.7, 'lon' => -74.0],
                    'shape'    => [
                        'type'        => 'envelope',
                        'coordinates' => [[-75.0, 41.0], [-70.0, 39.0]],
                    ],
                    'comments' => [
                        ['author' => 'alice', 'content' => 'Great guide'],
                    ],
                ],
            ],
            [
                'id' => '2',
                'body' => [
                    'title'    => 'PHP Development',
                    'content'  => 'Building web applications with php and elasticsearch',
                    'status'   => 'draft',
                    'price'    => 30.0,
                    'score'    => 6.0,
                    'rank'     => 2.1,
                    'popularity' => 80,
                    'tags'     => ['php', 'guide'],
                    'author'   => 'bob',
                    'color'    => 'blue',
                    'category' => 'development',
                    'created'  => '2024-02-20',
                    'location' => ['lat' => 41.0, 'lon' => -73.0],
                    'shape'    => [
                        'type'        => 'envelope',
                        'coordinates' => [[-74.0, 42.0], [-71.0, 40.0]],
                    ],
                    'comments' => [
                        ['author' => 'bob', 'content' => 'Nice tutorial'],
                    ],
                ],
            ],
            [
                'id' => '3',
                'body' => [
                    'title'    => 'Database Design Guide',
                    'content'  => 'Learn about database design patterns and search optimization',
                    'status'   => 'published',
                    'price'    => 35.0,
                    'score'    => 7.0,
                    'rank'     => 4.5,
                    'popularity' => 120,
                    'tags'     => ['database', 'search'],
                    'author'   => 'alice',
                    'color'    => 'green',
                    'category' => 'database',
                    'created'  => '2024-03-10',
                    'location' => ['lat' => 39.5, 'lon' => -71.0],
                    'shape'    => [
                        'type'        => 'envelope',
                        'coordinates' => [[-72.0, 40.0], [-68.0, 38.0]],
                    ],
                    'comments' => [
                        ['author' => 'alice', 'content' => 'Very helpful'],
                        ['author' => 'bob', 'content' => 'Learned a lot'],
                    ],
                ],
            ],
        ];

        foreach ($docs as $doc) {
            $client->index([
                'index' => $index,
                'id'    => $doc['id'],
                'body'  => $doc['body'],
            ]);
        }

        $client->indices()->refresh(['index' => $index]);
    }

    /**
     * Ensure special field mappings (percolator, rank_feature, shape).
     */
    protected static function ensureSpecialFields(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
    {
        $mapping = $client->indices()->getMapping(['index' => $index]);
        $properties = $mapping[$index]['mappings']['properties'] ?? [];

        $newFields = [];
        if (!isset($properties['query'])) {
            $newFields['query'] = ['type' => 'percolator'];
        }
        if (!isset($properties['pagerank'])) {
            $newFields['pagerank'] = ['type' => 'rank_feature'];
        }
        if (!isset($properties['cartesian_shape'])) {
            $newFields['cartesian_shape'] = ['type' => 'shape'];
        }

        if (!empty($newFields)) {
            $client->indices()->putMapping([
                'index' => $index,
                'body'  => ['properties' => $newFields],
            ]);

            if (isset($newFields['query'])) {
                $client->index([
                    'index' => $index,
                    'id'    => 'percolator_1',
                    'body'  => [
                        'query' => ['match' => ['title' => 'elasticsearch']],
                    ],
                ]);
            }

            $client->indices()->refresh(['index' => $index]);
        }
    }
}

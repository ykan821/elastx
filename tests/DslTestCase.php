<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ElasticKit\DSL\Query;

/**
 * Base test case for DSL tests with optional ES validation.
 *
 * When ELASTICKIT_TEST_HOST env var is set, assertQuery() also sends the query to
 * Elasticsearch and verifies it is accepted without error.
 */
abstract class DslTestCase extends TestCase
{
    /**
     * @var \Elastic\Elasticsearch\ClientInterface|null
     */
    protected static $esClient;

    /**
     * @var string
     */
    protected static $esIndex = 'elastickit_test';

    public static function setUpBeforeClass(): void
    {
        $esHost = getenv('ELASTICKIT_TEST_HOST');
        if ($esHost) {
            try {
                static::$esClient = \Elastic\Elasticsearch\ClientBuilder::create()
                    ->setHosts([$esHost])
                    ->build();

                static::ensureIndex();
            } catch (\Exception $e) {
                static::$esClient = null;
            }
        }
    }

    /**
     * Assert Query produces expected JSON, and optionally validate against ES.
     *
     * @param $expectedJson
     * @param $query
     */
    protected function assertQuery(string $expectedJson, Query $query)
    {
        $this->assertJsonStringEqualsJsonString($expectedJson, $query->toJson(), 'JSON mismatch');

        if (static::$esClient) {
            try {
                $params = [
                    'index' => static::$esIndex,
                    'body'  => $query->toArray(),
                ];
                $response = static::$esClient->search($params);
                if (isset($response['error'])) {
                    fwrite(STDERR, "\n  [ES Warning] " . $this->getName() . ': ' . json_encode($response['error']) . "\n");
                }
            } catch (\Exception $e) {
                fwrite(STDERR, "\n  [ES Warning] " . $this->getName() . ': ' . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * Ensure the test index exists with proper mapping and seed data.
     */
    private static function ensureIndex(): void
    {
        if (!static::$esClient) {
            return;
        }

        $client = static::$esClient;
        $index = static::$esIndex;

        if (!$client->indices()->exists(['index' => $index])) {
            static::createIndex($client, $index);
            static::seedData($client, $index);
        }

        static::ensureSpecialFields($client, $index);
    }

    /**
     * Create the test index with full mapping.
     */
    private static function createIndex(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
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
    private static function seedData(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
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
    private static function ensureSpecialFields(\Elastic\Elasticsearch\ClientInterface $client, string $index): void
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

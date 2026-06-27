<?php

declare(strict_types=1);

namespace Tests\Integration;

use Elastic\Elasticsearch\ClientBuilder;
use ElasticKit\DSL\Query;
use ElasticKit\Index\Support\ClientManager;
use ElasticKit\Index\Index;
use Tests\DslTestCase;

/**
 * Base for integration tests: one random ES index per test CLASS (shared by
 * its tests), reset between tests via deleteByQuery + re-seed. Skipped unless
 * ELASTICKIT_TEST_HOST is set. Reuses DslTestCase's createIndex/seedData.
 */
abstract class IntegrationTestCase extends DslTestCase
{
    /** @var array<class-string, string> test class -> its shared index name */
    private static array $indices = [];

    protected string $indexName;

    protected function setUp(): void
    {
        $host = getenv('ELASTICKIT_TEST_HOST');
        if (!$host) {
            $this->markTestSkipped('ELASTICKIT_TEST_HOST not set');
            return;
        }

        if (static::$esClient === null) {
            static::$esClient = ClientBuilder::create()->setHosts([$host])->build();
        }

        $class = static::class;
        if (!isset(self::$indices[$class])) {
            // first test of this class: create the index
            $this->indexName = 'ek_it_' . bin2hex(random_bytes(4));
            self::$indices[$class] = $this->indexName;
            static::createIndex(static::$esClient, $this->indexName);
        } else {
            // subsequent tests: clear docs left by the previous test
            $this->indexName = self::$indices[$class];
            static::$esClient->deleteByQuery([
                'index' => $this->indexName,
                'body' => ['query' => ['match_all' => new \stdClass()]],
                'refresh' => true,
            ]);
        }

        // fresh seed for every test (cheap vs. creating the index)
        static::seedData(static::$esClient, $this->indexName);

        Index::setClient(static::$esClient);
    }

    protected function tearDown(): void
    {
        ClientManager::reset();
    }

    public static function tearDownAfterClass(): void
    {
        $class = static::class;
        if (isset(self::$indices[$class]) && static::$esClient !== null) {
            try {
                static::$esClient->indices()->delete(['index' => self::$indices[$class]]);
            } catch (\Throwable $e) {
                // best-effort cleanup
            }
            unset(self::$indices[$class]);
        }
    }

    /**
     * Anonymous Index subclass bound to the shared test index.
     */
    protected function makeIndex(): Index
    {
        $name = $this->indexName;
        return new class ($name) extends Index {
            public function __construct(string $name)
            {
                $this->name = $name;
            }
        };
    }

    /**
     * Send the query to ES; assert it is accepted. Optionally assert hit count.
     *
     * @return array<string, mixed> raw ES response
     */
    protected function assertQueryEs(Query $query, ?int $expectedHits = null): array
    {
        $response = static::$esClient->search([
            'index' => $this->indexName,
            'body'  => $query->toArray(),
        ])->asArray();

        if ($expectedHits !== null) {
            $this->assertSame(
                $expectedHits,
                $response['hits']['total']['value'] ?? 0,
                'Hit count mismatch for query: ' . json_encode($query->toArray())
            );
        }

        return $response;
    }

    /**
     * Refresh the shared index so writes are immediately searchable.
     */
    protected function refreshIndex(): void
    {
        static::$esClient->indices()->refresh(['index' => $this->indexName]);
    }
}

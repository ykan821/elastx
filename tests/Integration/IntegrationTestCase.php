<?php

declare(strict_types=1);

namespace Tests\Integration;

use Elastic\Elasticsearch\ClientBuilder;
use ElasticKit\DSL\Query;
use ElasticKit\Index\ClientManager;
use ElasticKit\Index\Index;
use Tests\DslTestCase;

/**
 * Base for integration tests: each test gets an isolated random ES index.
 *
 * Skipped unless ELASTICKIT_TEST_HOST is set. Reuses DslTestCase's
 * createIndex/seedData helpers for a shared mapping/seed contract.
 */
abstract class IntegrationTestCase extends DslTestCase
{
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

        $this->indexName = 'ek_it_' . bin2hex(random_bytes(4));
        static::createIndex(static::$esClient, $this->indexName);
        static::seedData(static::$esClient, $this->indexName);

        Index::setClient(static::$esClient);
    }

    protected function tearDown(): void
    {
        if (static::$esClient !== null && isset($this->indexName)) {
            try {
                static::$esClient->indices()->delete(['index' => $this->indexName]);
            } catch (\Throwable $e) {
                // best-effort cleanup; ignore 404 if index already gone
            }
        }
        ClientManager::reset();
    }

    /**
     * Anonymous Index subclass bound to the random test index.
     */
    protected function makeIndex(): Index
    {
        $name = $this->indexName;
        return new class($name) extends Index {
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
     * Refresh the random index so writes are immediately searchable.
     */
    protected function refreshIndex(): void
    {
        static::$esClient->indices()->refresh(['index' => $this->indexName]);
    }
}

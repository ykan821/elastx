<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use Tests\Integration\IntegrationTestCase;

class AggregationContractTest extends IntegrationTestCase
{
    public function testTermsAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('by_status', ['terms' => ['field' => 'status']]);
        $r = $this->assertQueryEs($q);
        $buckets = array_column($r['aggregations']['by_status']['buckets'] ?? [], null, 'key');
        $this->assertSame(2, $buckets['published']['doc_count'] ?? null);
        $this->assertSame(1, $buckets['draft']['doc_count'] ?? null);
    }

    public function testSumAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('total_price', ['sum' => ['field' => 'price']]);
        $r = $this->assertQueryEs($q);
        $this->assertEquals(90.0, $r['aggregations']['total_price']['value'] ?? null);
    }

    public function testAvgAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('avg_score', ['avg' => ['field' => 'score']]);
        $r = $this->assertQueryEs($q);
        // (8.5 + 6.0 + 7.0) / 3 ≈ 7.17
        $this->assertEqualsWithDelta(7.17, $r['aggregations']['avg_score']['value'] ?? 0, 0.01);
    }

    public function testStatsAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('price_stats', ['stats' => ['field' => 'price']]);
        $r = $this->assertQueryEs($q);
        $stats = $r['aggregations']['price_stats'] ?? [];
        $this->assertSame(3, $stats['count'] ?? null);
        $this->assertEquals(25.0, $stats['min'] ?? null);
        $this->assertEquals(35.0, $stats['max'] ?? null);
    }

    public function testCardinalityAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('authors', ['cardinality' => ['field' => 'author']]);
        $r = $this->assertQueryEs($q);
        $this->assertSame(2, $r['aggregations']['authors']['value'] ?? null);
    }

    public function testDateHistogramAgg(): void
    {
        $q = (new Query())->matchAll();
        $q->aggs('by_month', ['date_histogram' => ['field' => 'created', 'calendar_interval' => 'month']]);
        $r = $this->assertQueryEs($q);
        $this->assertCount(3, $r['aggregations']['by_month']['buckets'] ?? []);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Span\SpanNear;
use ElasticKit\DSL\Queries\Span\SpanOr;
use Tests\Integration\IntegrationTestCase;

/**
 * Span query contracts against a real Elasticsearch (the shared seeded index).
 *
 * Seeded content:
 *  - doc 1: "...elasticsearch ... database design"
 *  - doc 2: "...php ... elasticsearch"
 *  - doc 3: "...database design ... search ..."
 */
class SpanContractTest extends IntegrationTestCase
{
    public function testSpanTerm(): void
    {
        // "elasticsearch" occurs in docs 1 and 2
        $this->assertQueryEs((new Query())->spanTerm('content', 'elasticsearch'), 2);
    }

    public function testSpanNear(): void
    {
        // "database design" adjacent in docs 1 and 3
        $q = (new Query())->spanNear(function (SpanNear $near) {
            $near->clauses(function (Query $query) {
                $query->spanTerm('content', 'database');
                $query->spanTerm('content', 'design');
            })->slop(1)->inOrder(true);
        });
        $this->assertQueryEs($q, 2);
    }

    public function testSpanOr(): void
    {
        // elasticsearch (docs 1,2) or php (doc 2) -> docs 1,2
        $q = (new Query())->spanOr(function (SpanOr $or) {
            $or->clauses(function (Query $query) {
                $query->spanTerm('content', 'elasticsearch');
                $query->spanTerm('content', 'php');
            });
        });
        $this->assertQueryEs($q, 2);
    }
}

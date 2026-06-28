<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Compound\ConstantScore;
use ElasticKit\DSL\Queries\TermLevel\Range;
use Tests\Integration\IntegrationTestCase;

class CompoundContractTest extends IntegrationTestCase
{
    public function testBoolMust(): void
    {
        // published AND green -> doc 3
        $q = (new Query())->bool(function (Boolean $b) {
            $b->must(function (Query $q) {
                $q->term('status', 'published');
            })->must(function (Query $q) {
                $q->term('color', 'green');
            });
        });
        $this->assertQueryEs($q, 1);
    }

    public function testBoolShould(): void
    {
        // alice (1,3) OR bob (2) -> 3
        $q = (new Query())->bool(function (Boolean $b) {
            $b->should(function (Query $q) {
                $q->term('author', 'alice');
            })->should(function (Query $q) {
                $q->term('author', 'bob');
            })->minimumShouldMatch(1);
        });
        $this->assertQueryEs($q, 3);
    }

    public function testBoolFilter(): void
    {
        // price >= 30 -> docs 2,3
        $q = (new Query())->bool(function (Boolean $b) {
            $b->filter(function (Query $q) {
                $q->range('price', function (Range $r) {
                    $r->gte(30);
                });
            });
        });
        $this->assertQueryEs($q, 2);
    }

    public function testBoolMustNot(): void
    {
        // all except published -> doc 2 (draft)
        $q = (new Query())->bool(function (Boolean $b) {
            $b->must(function (Query $q) {
                $q->matchAll();
            })->mustNot(function (Query $q) {
                $q->term('status', 'published');
            });
        });
        $this->assertQueryEs($q, 1);
    }

    public function testConstantScore(): void
    {
        $q = (new Query())->constantScore(function (ConstantScore $c) {
            $c->filter(function (Query $q) {
                $q->term('status', 'published');
            });
        });
        $this->assertQueryEs($q, 2);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Script;
use ElasticKit\DSL\Queries\Specialized\Script as ScriptQuery;
use ElasticKit\DSL\Queries\Specialized\ScriptScore;
use Tests\Integration\IntegrationTestCase;

class SpecializedContractTest extends IntegrationTestCase
{
    public function testScript(): void
    {
        // doc['price'].value > 29 -> docs 2 (30), 3 (35)
        $q = (new Query())->script(function (ScriptQuery $s) {
            $s->script(function (Script $script) {
                $script->source("doc['price'].value > 29");
            });
        });
        $this->assertQueryEs($q, 2);
    }

    public function testScriptScore(): void
    {
        // score = doc['score'].value; doc 1 (8.5) ranks first
        $q = (new Query())->scriptScore(function (ScriptScore $ss) {
            $ss->query(function (Query $query) {
                $query->matchAll();
            })->script(function (Script $script) {
                $script->source("doc['score'].value");
            });
        });
        $r = $this->assertQueryEs($q, 3);
        $this->assertSame('1', $r['hits']['hits'][0]['_id']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Joining\Nested;
use Tests\Integration\IntegrationTestCase;

class JoiningContractTest extends IntegrationTestCase
{
    public function testNestedMatch(): void
    {
        // "helpful" in comments.content -> doc 3 ("Very helpful")
        $q = (new Query())->nested(function (Nested $n) {
            $n->path('comments')->query(function (Query $q) {
                $q->match('comments.content', 'helpful');
            });
        });
        $this->assertQueryEs($q, 1);
    }

    public function testNestedTerm(): void
    {
        // bob in comments.author -> docs 2, 3
        $q = (new Query())->nested(function (Nested $n) {
            $n->path('comments')->query(function (Query $q) {
                $q->term('comments.author', 'bob');
            });
        });
        $this->assertQueryEs($q, 2);
    }
}

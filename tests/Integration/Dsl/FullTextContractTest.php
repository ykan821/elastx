<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\FullText\MatchBoolPrefix;
use ElasticKit\DSL\Queries\FullText\QueryString;
use Tests\Integration\IntegrationTestCase;

class FullTextContractTest extends IntegrationTestCase
{
    public function testMatch(): void
    {
        // content contains "elasticsearch" in docs 1 and 2
        $this->assertQueryEs((new Query())->match('content', 'elasticsearch'), 2);
    }

    public function testMatchPhrase(): void
    {
        // "database design" appears in docs 1 and 3 content
        $this->assertQueryEs((new Query())->matchPhrase('content', ['query' => 'database design']), 2);
    }

    public function testQueryString(): void
    {
        $q = (new Query())->queryString(function (QueryString $qs) {
            $qs->query('status:published AND color:green');
        });
        $this->assertQueryEs($q, 1);
    }

    public function testMatchBoolPrefix(): void
    {
        // authors starting with "al" -> alice (docs 1,3)
        $q = (new Query())->matchBoolPrefix('author', function (MatchBoolPrefix $m) {
            $m->query('al');
        });
        $this->assertQueryEs($q, 2);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Fuzzy;
use ElasticKit\DSL\Queries\TermLevel\Prefix;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Wildcard;
use Tests\Integration\IntegrationTestCase;

class TermLevelContractTest extends IntegrationTestCase
{
    public function testTerm(): void
    {
        $this->assertQueryEs((new Query())->term('status', 'published'), 2);
    }

    public function testTerms(): void
    {
        $this->assertQueryEs((new Query())->terms('color', ['red', 'blue']), 2);
    }

    public function testRange(): void
    {
        $q = (new Query())->range('price', function (Range $r) {
            $r->gte(25)->lte(30);
        });
        $this->assertQueryEs($q, 2);
    }

    public function testExists(): void
    {
        $this->assertQueryEs((new Query())->exists('category'), 3);
    }

    public function testPrefix(): void
    {
        $q = (new Query())->prefix('author', function (Prefix $p) {
            $p->value('al');
        });
        $this->assertQueryEs($q, 2);
    }

    public function testWildcard(): void
    {
        $q = (new Query())->wildcard('author', function (Wildcard $w) {
            $w->value('b*');
        });
        $this->assertQueryEs($q, 1);
    }

    public function testFuzzy(): void
    {
        $q = (new Query())->fuzzy('author', function (Fuzzy $f) {
            $f->value('alic');
        });
        $this->assertQueryEs($q, 2);
    }

    public function testIds(): void
    {
        $this->assertQueryEs((new Query())->ids(['1', '3']), 2);
    }
}

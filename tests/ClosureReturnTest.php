<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\FullText\Match_;

class ClosureReturnTest extends DslTestCase
{
    public function testClosureModifiesParameter()
    {
        $query = new Query();
        $query->match('title', function ($m) {
            $m->query('test');
        });

        $this->assertQuery('{"query":{"match":{"title":{"query":"test"}}}}', $query);
    }

    public function testClosureReturnsNullFallsBackToParameter()
    {
        $query = new Query();
        $query->match('title', function ($m) {
            $m->query('test');
            return null;
        });

        $this->assertQuery('{"query":{"match":{"title":{"query":"test"}}}}', $query);
    }

    public function testClosureReturnsScalarFallsBackToParameter()
    {
        $query = new Query();
        $query->match('title', function ($m) {
            $m->query('test');
            return 123;
        });

        $this->assertQuery('{"query":{"match":{"title":{"query":"test"}}}}', $query);
    }

    public function testClosureReturnsWrongTypeFallsBackToParameter()
    {
        $query = new Query();
        $query->match('title', function ($m) {
            $m->query('test');
            return new \stdClass();
        });

        $this->assertQuery('{"query":{"match":{"title":{"query":"test"}}}}', $query);
    }

    public function testTypeEmptyClosureProducesEmptyField()
    {
        $query = new Query();
        $query->match('title', function () {
        });

        $this->assertQuery('{"query":{"match":{"title":null}}}', $query);
    }

    public function testEmptyMustClosureKeepsEmptyArray()
    {
        $query = new Query();
        $query->bool(['must' => function () {
        }]);

        $this->assertQuery('{"query":{"bool":{"must":[]}}}', $query);
    }

    public function testBoolClosureReturnsNewQuery()
    {
        $query = new Query();
        $query->bool(['must' => function ($q) {
            $q->match('title', 'test');
        }]);

        $this->assertQuery('{"query":{"bool":{"must":[{"match":{"title":"test"}}]}}}', $query);
    }

    public function testBoolArrayFormClosureReturnsNewQuery()
    {
        $query = new Query();
        $query->bool([
            'must' => function ($q) {
                $q->match('title', 'test');
            },
        ]);

        $this->assertQuery('{"query":{"bool":{"must":[{"match":{"title":"test"}}]}}}', $query);
    }
}

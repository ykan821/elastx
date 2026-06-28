<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\Compound\Boolean;

/**
 * Tests that polymorphic inputs (closure, string/array, object) produce identical DSL.
 */
class PolymorphicInputTest extends DslTestCase
{
    // --- Field-wrapping nodes: 4 input forms ---

    public function testMatchClosure()
    {
        $query = new Query();
        $query->match('title', function (Match_ $m) {
            $m->query('test')->fuzziness('AUTO');
        });
        $this->assertQuery('{"query":{"match":{"title":{"query":"test","fuzziness":"AUTO"}}}}', $query);
    }

    public function testMatchString()
    {
        $query = new Query();
        $query->match('title', 'test');
        $this->assertQuery('{"query":{"match":{"title":"test"}}}', $query);
    }

    public function testMatchArray()
    {
        $query = new Query();
        $query->match('title', ['query' => 'test', 'fuzziness' => 'AUTO']);
        $this->assertQuery('{"query":{"match":{"title":{"query":"test","fuzziness":"AUTO"}}}}', $query);
    }

    public function testMatchObject()
    {
        $m = new Match_();
        $m->field('title')->query('test')->fuzziness('AUTO');
        $query = new Query();
        $query->match($m);
        $this->assertQuery('{"query":{"match":{"title":{"query":"test","fuzziness":"AUTO"}}}}', $query);
    }

    public function testDuplicateClauseKeyThrows()
    {
        $query = new Query();
        $query->match('title', 'A');
        $query->match('content', 'B');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate query clause key "match"');
        $query->toArray();
    }

    public function testTermClosure()
    {
        $query = new Query();
        $query->term('status', function (Term $t) {
            $t->value('published');
        });
        $this->assertQuery('{"query":{"term":{"status":{"value":"published"}}}}', $query);
    }

    public function testTermString()
    {
        $query = new Query();
        $query->term('status', 'published');
        $this->assertQuery('{"query":{"term":{"status":"published"}}}', $query);
    }

    public function testTermArray()
    {
        $query = new Query();
        $query->term('status', ['value' => 'published', 'boost' => 2.0]);
        $this->assertQuery('{"query":{"term":{"status":{"value":"published","boost":2.0}}}}', $query);
    }

    public function testTermObject()
    {
        $t = new Term();
        $t->field('status')->value('published');
        $query = new Query();
        $query->term($t);
        $this->assertQuery('{"query":{"term":{"status":{"value":"published"}}}}', $query);
    }

    public function testRangeClosure()
    {
        $query = new Query();
        $query->range('price', function (Range $r) {
            $r->gte(10)->lte(50);
        });
        $this->assertQuery('{"query":{"range":{"price":{"gte":10,"lte":50}}}}', $query);
    }

    public function testRangeArray()
    {
        $query = new Query();
        $query->range('price', ['gte' => 10, 'lte' => 50]);
        $this->assertQuery('{"query":{"range":{"price":{"gte":10,"lte":50}}}}', $query);
    }

    public function testRangeObject()
    {
        $r = new Range();
        $r->field('price')->gte(10)->lte(50);
        $query = new Query();
        $query->range($r);
        $this->assertQuery('{"query":{"range":{"price":{"gte":10,"lte":50}}}}', $query);
    }

    // --- Non-field-wrapping nodes ---

    public function testBoolClosure()
    {
        $query = new Query();
        $query->bool(function (Boolean $b) {
            $b->must(function (Query $q) {
                $q->match('title', 'test');
            });
        });
        $this->assertQuery('{"query":{"bool":{"must":[{"match":{"title":"test"}}]}}}', $query);
    }

    public function testBoolArrayForm()
    {
        $query = new Query();
        $query->bool([
            'must' => function (Query $q) {
                $q->match('title', 'test');
            },
        ]);
        $this->assertQuery('{"query":{"bool":{"must":[{"match":{"title":"test"}}]}}}', $query);
    }

    public function testBoolObject()
    {
        $b = new Boolean();
        $b->must(function (Query $q) {
            $q->match('title', 'test');
        });
        $query = new Query();
        $query->bool($b);
        $this->assertQuery('{"query":{"bool":{"must":[{"match":{"title":"test"}}]}}}', $query);
    }

    // --- Constructor two-arg mode ---

    public function testTermConstructorTwoArg()
    {
        $t = new Term('status', 'published');
        $this->assertEquals(['status' => 'published'], $t->toArray());
    }

    public function testMatchConstructorTwoArg()
    {
        $m = new Match_('title', 'test');
        $this->assertEquals(['title' => 'test'], $m->toArray());
    }

    // --- Scalar chaining: _rawValue promotion ---

    public function testTermScalarChainPromotesToValueKey()
    {
        $t = new Term('status', 'published');
        $t->boost(2.0);
        $query = new Query();
        $query->term($t);
        $this->assertQuery('{"query":{"term":{"status":{"value":"published","boost":2.0}}}}', $query);
    }

    public function testMatchScalarChainPromotesToQueryKey()
    {
        $m = new Match_('title', 'test');
        $m->fuzziness('AUTO');
        $query = new Query();
        $query->match($m);
        $this->assertQuery('{"query":{"match":{"title":{"query":"test","fuzziness":"AUTO"}}}}', $query);
    }

    // --- Boundary: create() instance reuse ---

    public function testCreateReturnsSameInstance()
    {
        $t = new Term();
        $t->field('status')->value('published');
        $this->assertSame($t, Term::create($t));
    }

    public function testQueryCreateReturnsSameInstance()
    {
        $q = new Query();
        $q->match('title', 'test');
        $this->assertSame($q, Query::create($q));
    }

    // --- Boundary: array shorthand for field-wrapping nodes ---

    public function testMatchArrayShorthand()
    {
        $query = new Query();
        $query->match(['title' => 'test']);
        $this->assertQuery('{"query":{"match":{"title":"test"}}}', $query);
    }

    public function testTermArrayShorthand()
    {
        $query = new Query();
        $query->term(['status' => 'published']);
        $this->assertQuery('{"query":{"term":{"status":"published"}}}', $query);
    }

    public function testRangeArrayShorthand()
    {
        $query = new Query();
        $query->range(['price' => ['gte' => 10, 'lte' => 50]]);
        $this->assertQuery('{"query":{"range":{"price":{"gte":10,"lte":50}}}}', $query);
    }

    // --- Boundary: Query polymorphic inputs ---

    public function testQueryCreateWithClosure()
    {
        $q = Query::create(function (Query $q) {
            $q->match('title', 'test');
        });
        $this->assertEquals(['match' => ['title' => 'test']], $q->toArray()['query']);
    }

    public function testQueryCreateWithNode()
    {
        $m = new Match_();
        $m->field('title')->query('test');
        $q = Query::create($m);
        $this->assertEquals(['match' => ['title' => ['query' => 'test']]], $q->toArray()['query']);
    }

    public function testQueryCreateWithArrayWithQueryKey()
    {
        $q = Query::create(['query' => ['match' => ['title' => 'test']]]);
        $this->assertEquals(['match' => ['title' => 'test']], $q->toArray()['query']);
    }

    public function testQueryCreateWithArrayWithoutQueryKey()
    {
        $q = Query::create(['match' => ['title' => 'test']]);
        $this->assertEquals(['match' => ['title' => 'test']], $q->toArray()['query']);
    }

}

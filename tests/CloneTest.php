<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ElasticKit\DSL\Agg;
use ElasticKit\DSL\Query;

/**
 * Deep-clone isolation for Query and Agg (nested objects must not be shared).
 */
class CloneTest extends TestCase
{
    public function testQueryDeepClausesDoNotShareReferences()
    {
        $original = (new Query())->match('title', 'foo');
        $clone = clone $original;

        $this->assertNotSame($original->getQueries()[0], $clone->getQueries()[0]);

        // mutating the clone's clause must not leak into the original
        $before = $original->toArray();
        $clone->getQueries()[0]->boost(2.0);
        $this->assertSame($before, $original->toArray());
    }

    public function testQueryDeepClonesAggregations()
    {
        $original = (new Query())->aggs('by_status', Agg::create()->terms('status'));
        $clone = clone $original;

        $this->assertNotSame(
            $this->prop($original, '_aggregations')['by_status'],
            $this->prop($clone, '_aggregations')['by_status']
        );
    }

    public function testAggDeepClonesNodeAndSubAggs()
    {
        $original = (new Agg())->terms('status');
        $original->aggs('avg_price', Agg::create()->avg('price'));
        $clone = clone $original;

        $this->assertNotSame($this->prop($original, '_node'), $this->prop($clone, '_node'));
        $this->assertNotSame(
            $this->prop($original, '_subAggs')['avg_price'],
            $this->prop($clone, '_subAggs')['avg_price']
        );
    }

    public function testQueryMutationOnCloneDoesNotLeakToOriginal()
    {
        $original = (new Query())->match('title', 'foo');
        $before = $original->toArray();

        $clone = clone $original;
        $clone->match('content', 'bar');            // add to clone only
        $clone->getQueries()[0]->boost(3.0);        // mutate clone's first clause

        $this->assertSame($before, $original->toArray());
    }

    /**
     * @return mixed
     */
    private function prop(object $object, string $name)
    {
        return (new \ReflectionProperty($object, $name))->getValue($object);
    }
}

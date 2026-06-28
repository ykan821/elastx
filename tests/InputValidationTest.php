<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;

/**
 * Input-validation behaviour: invalid input throws (or is treated as a value)
 * rather than silently producing invalid DSL.
 */
class InputValidationTest extends DslTestCase
{
    public function testAggsRejectsNullAlias()
    {
        $this->expectException(\BadMethodCallException::class);
        (new Query())->aggs(null, ['avg' => ['field' => 'price']]);
    }

    public function testAggsRejectsEmptyStringAlias()
    {
        $this->expectException(\BadMethodCallException::class);
        (new Query())->aggs('', ['avg' => ['field' => 'price']]);
    }

    public function testWhenTreatsStringAsTruthyNotInvoked()
    {
        // 'count' must NOT be invoked as a function; it is a truthy value.
        $query = (new Query())->when('count', fn (Query $q) => $q->match('title', 'x'));

        $this->assertNotEmpty($query->getQueries());
    }

    public function testWhenFalseSkipsTheClause()
    {
        $query = (new Query())->when(false, fn (Query $q) => $q->match('title', 'x'));

        $this->assertEmpty($query->getQueries());
    }
}

<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;

class MatchAllTest extends DslTestCase
{
    public function testMatchAll()
    {
        $exampleJson = <<<'JSON'
{
    "query": {
        "match_all": {}
    }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $this->assertQuery($exampleJson, $query);
    }

    public function testMatchNone()
    {
        $exampleJson = <<<'JSON'
{
    "query": {
        "match_none": {}
    }
}
JSON;
        $query = new Query();
        $query->matchNone();
        $this->assertQuery($exampleJson, $query);
    }
}

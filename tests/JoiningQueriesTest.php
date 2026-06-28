<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Compound\FunctionScore;
use ElasticKit\DSL\Queries\Compound\Functions\Function_;
use ElasticKit\DSL\Queries\Compound\Functions\ScriptScore;
use ElasticKit\DSL\Queries\Joining\HasChild;
use ElasticKit\DSL\Queries\Joining\HasParent;
use ElasticKit\DSL\Queries\Joining\Nested;
use ElasticKit\DSL\Queries\Joining\ParentId;

class JoiningQueriesTest extends DslTestCase
{
    public function testNested()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "nested": {
      "path": "driver",
      "query": {
        "nested": {
          "path": "driver.vehicle",
          "query": {
            "bool": {
              "must": [
                { "match": { "driver.vehicle.make": "Powell Motors" } },
                { "match": { "driver.vehicle.model": "Canyonero" } }
              ]
            }
          },
          "score_mode": "avg"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->nested(function (Nested $nested) {
            $nested->path('driver');
            $nested->query(function (Query $q) {
                $q->nested(function (Nested $n) {
                    $n->path('driver.vehicle');
                    $n->query(function (Query $q) {
                        $q->bool(function (Boolean $boolean) {
                            $boolean->must(function (Query $q) {
                                $q->match('driver.vehicle.make', 'Powell Motors');
                                $q->match('driver.vehicle.model', 'Canyonero');
                            });
                        });
                    })
                    ->scoreMode('avg');
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testHasChild()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "has_child": {
      "type": "child",
      "query": {
        "match_all": {}
      },
      "max_children": 10,
      "min_children": 2,
      "score_mode": "min"
    }
  }
}
JSON;

        $query = new Query();
        $query->hasChild(function (HasChild $hasChild) {
            $hasChild->type('child');
            $hasChild->query(function (Query $q) {
                $q->matchAll();
            })
            ->maxChildren(10)
            ->minChildren(2)
            ->scoreMode('min');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testHasParent()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "has_parent": {
      "parent_type": "parent",
      "score": true,
      "query": {
        "function_score": {
          "functions": [
            {
              "script_score": {
                "script": "_score * doc['view_count'].value"
              }
            }
          ]
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->hasParent(function (HasParent $hasParent) {
            $hasParent->parentType('parent');
            $hasParent->score(true);
            $hasParent->query(function (Query $q) {
                $q->functionScore(function (FunctionScore $fs) {
                    $fs->addFunction(function (Function_ $fb) {
                        $fb->scriptScore(function (ScriptScore $ss) {
                            $ss->script('_score * doc[\'view_count\'].value');
                        });
                    });
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testParentId()
    {
        $exampleJson = <<<JSON
{
  "query": {
      "parent_id": {
          "type": "my-child",
          "id": "1"
      }
  }
}
JSON;
        $query = new Query();
        $query->parentId(function (ParentId $parentId) {
            $parentId->type('my-child');
            $parentId->id('1');
        });
        $this->assertQuery($exampleJson, $query);
    }
}

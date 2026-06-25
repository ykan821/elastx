<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Specialized\MoreLikeThis;
use ElasticKit\DSL\Queries\Specialized\Percolate;
use ElasticKit\DSL\Queries\Specialized\Pinned;
use ElasticKit\DSL\Queries\Specialized\RankFeature;
use ElasticKit\DSL\Queries\Specialized\Script;
use ElasticKit\DSL\Queries\Specialized\ScriptScore;
use ElasticKit\DSL\Queries\Specialized\Wrapper;

class SpecializedQueriesTest extends DslTestCase
{
    public function testDistanceFeature()
    {
$expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        { "match": { "name": "chocolate" } }
      ],
      "should": [
        { "distance_feature": { "field": "production_date", "pivot": "7d", "origin": "now" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(function (Boolean $bool) {
            $bool->must(function (Query $query) {
                $query->match('name', 'chocolate');
            });
            $bool->should(function (Query $query) {
                $query->distanceFeature([
                    'field' => 'production_date',
                    'pivot' => '7d',
                    'origin' => 'now',
                ]);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testMoreLikeThis()
    {
$exampleJson = <<<JSON
{
  "query": {
    "more_like_this" : {
      "fields" : ["title", "description"],
      "like" : "Once upon a time",
      "min_term_freq" : 1,
      "max_query_terms" : 12
    }
  }
}
JSON;
        $query = new Query();
        $query->moreLikeThis(function (MoreLikeThis $moreLikeThis) {
            $moreLikeThis->fields(['title', 'description']);
            $moreLikeThis->like('Once upon a time');
            $moreLikeThis->minTermFreq(1);
            $moreLikeThis->maxQueryTerms(12);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testPercolate()
    {
$exampleJson = <<<JSON
{
  "query": {
    "percolate": {
      "field": "query",
      "document": {
        "message": "A new bonsai tree in the office"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->percolate(function (Percolate $percolate) {
            $percolate->field('query');
            $percolate->document(['message' => 'A new bonsai tree in the office']);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testRankFeature()
    {
$exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "content": "2016"
          }
        }
      ],
      "should": [
        {
          "rank_feature": {
            "field": "url_length",
            "boost": 0.1
          }
        },
        {
          "rank_feature": {
            "field": "topics.culture",
            "saturation": {
              "pivot": 8
            }
          }
        },
        {
           "rank_feature": {
             "field": "pagerank",
             "linear": {}
           }
        }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(function (Boolean $bool) {
            $bool->must(function (Query $query) {
                $query->match('content', '2016');
            });
            $bool->should(function (Query $query) {
                $query->rankFeature(function (RankFeature $rankFeature) {
                    $rankFeature->field('url_length');
                    $rankFeature->boost(0.1);
                });
                $query->rankFeature(function (RankFeature $rankFeature) {
                    $rankFeature->field('topics.culture');
                    $rankFeature->saturation(['pivot' => 8]);
                });
                $query->rankFeature(function (RankFeature $rankFeature) {
                    $rankFeature->field('pagerank');
                    $rankFeature->linear((object)[]);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testScript()
    {
$exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "filter": [
      {
        "script": {
          "script": {
            "source": "doc['num1'].value > params.param1",
            "lang": "painless",
            "params": {
              "param1": 5
            }
          }
        }
      }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(function (Boolean $bool) {
            $bool->filter(function (Query $query) {
                $query->script(function (Script $script) {
                    $script->script(function (\ElasticKit\DSL\Queries\Script $script) {
                        $script->source('doc[\'num1\'].value > params.param1');
                        $script->lang('painless');
                        $script->params(['param1' => 5]);
                    });
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testScriptScore()
    {
$exampleJson = <<<JSON
{
  "query": {
    "script_score": {
      "query": {
        "match": { "message": "elasticsearch" }
      },
      "script": {
        "source": "doc['my-int'].value / 10 "
      },
      "min_score": 5.5
    }
  }
}
JSON;
        $query = new Query();
        $query->scriptScore(function (ScriptScore $scriptScore) {
            $scriptScore->query(function (Query $query) {
                $query->match('message', 'elasticsearch');
            });
            $scriptScore->script(function (\ElasticKit\DSL\Queries\Script $script) {
                $script->source('doc[\'my-int\'].value / 10 ');
            });
            $scriptScore->minScore(5.5);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testWrapper()
    {
$exampleJson = <<<JSON
{
  "query": {
    "wrapper": {
      "query": "eyJ0ZXJtIiA6IHsgInVzZXIuaWQiIDogImtpbWNoeSIgfX0=" 
    }
  }
}
JSON;
        $query = new Query();
        $query->wrapper(function (Wrapper $wrapper) {
            $wrapper->query('eyJ0ZXJtIiA6IHsgInVzZXIuaWQiIDogImtpbWNoeSIgfX0=');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testPinned()
    {
$exampleJson = <<<JSON
{
  "query": {
    "pinned": {
      "ids": [ "1", "4", "100" ],
      "organic": {
        "match": {
          "description": "iphone"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->pinned(function (Pinned $pinned) {
            $pinned->ids(['1', '4', '100']);
            $pinned->organic(function (Query $query) {
                $query->match('description', 'iphone');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

}
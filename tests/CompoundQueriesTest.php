<?php


use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Compound\Boosting;
use ElasticKit\DSL\Queries\Compound\ConstantScore;
use ElasticKit\DSL\Queries\Compound\DisjunctionMax;
use ElasticKit\DSL\Queries\Compound\Functions\FieldValueFactor;
use ElasticKit\DSL\Queries\Compound\Functions\Function_;
use ElasticKit\DSL\Queries\Compound\Functions\Gauss;
use ElasticKit\DSL\Queries\Compound\Functions\Exp;
use ElasticKit\DSL\Queries\Compound\Functions\Linear;
use ElasticKit\DSL\Queries\Compound\Functions\RandomScore;
use ElasticKit\DSL\Queries\Compound\FunctionScore;
use ElasticKit\DSL\Queries\Compound\Functions\ScriptScore;
use ElasticKit\DSL\Queries\Script;

class CompoundQueriesTest extends DslTestCase
{
    public function testBool()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        { "match": { "title": "test" } }
      ],
      "should": [
        { "match": { "title": "test" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(function (Boolean $q) {
            $q->must(function (Query $q) {
                $q->match(['title' => 'test']);
            });
            $q->should(function (Query $q) {
                $q->match(['title' => 'test']);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testBool2()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must_not": [
        { "match": { "title": "test" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(['must_not' => [['match' => ['title' => 'test']]]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoolShorthand()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "should": [
        { "term": { "mobile": "13800138000" } },
        { "term": { "id_card": "13800138000" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(['should' => function (Query $q) {
            $q->term('mobile', '13800138000');
            $q->term('id_card', '13800138000');
        }]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoolArrayWithClosures()
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
        $query->bool([
            'must' => function (Query $q) {
                $q->match('name', 'chocolate');
            },
            'should' => function (Query $q) {
                $q->distanceFeature([
                    'field' => 'production_date',
                    'pivot' => '7d',
                    'origin' => 'now',
                ]);
            },
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoolArrayWithQueryObject()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        { "term": { "is_deleted": 0 } },
        { "term": { "status": 1 } }
      ]
    }
  }
}
JSON;
        $innerQuery = new Query();
        $innerQuery->term('is_deleted', 0);
        $innerQuery->term('status', 1);
        $query = new Query();
        $query->bool(['must' => $innerQuery]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoosting()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "boosting": {
      "negative": { "match": { "title": "test" } },
      "positive": { "match": { "title": "test" } },
      "negative_boost": 0.5
    }
  }
}
JSON;
        $query = new Query();
        $query->boosting(function (Boosting $boosting) {
            $boosting->negative(function (Query $q) {
                $q->match(['title' => 'test']);
            });
            $boosting->positive(function (Query $q) {
                $q->match(['title' => 'test']);
            });
            $boosting->negativeBoost(0.5);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoostingArrayWithClosures()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "boosting": {
      "positive": { "term": { "status": "published" } },
      "negative": { "term": { "status": "draft" } },
      "negative_boost": 0.5
    }
  }
}
JSON;
        $query = new Query();
        $query->boosting([
            'positive' => function (Query $q) {
                $q->term('status', 'published');
            },
            'negative' => function (Query $q) {
                $q->term('status', 'draft');
            },
            'negative_boost' => 0.5,
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testDisjunction()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "dis_max": {
      "queries": [
        { "term": { "title": "Quick pets" } },
        { "term": { "body": "Quick pets" } }
      ],
      "tie_breaker": 0.7
    }
  }
}
JSON;
        $query = new Query();
        $query->disMax(function (DisjunctionMax $disjunctionMax) {
            $disjunctionMax->queries(function (Query $q) {
                $q->term(['title' => 'Quick pets']);
                $q->term(['body' => 'Quick pets']);
            })->tieBreaker(0.7);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testDisMaxArrayWithClosures()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "dis_max": {
      "queries": [
        { "term": { "title": "Quick pets" } },
        { "term": { "body": "Quick pets" } }
      ],
      "tie_breaker": 0.7
    }
  }
}
JSON;
        $query = new Query();
        $query->disMax([
            'queries' => function (Query $q) {
                $q->term(['title' => 'Quick pets']);
                $q->term(['body' => 'Quick pets']);
            },
            'tie_breaker' => 0.7,
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testFunctionScore()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "query": { "match_all": {} },
      "boost": "5",
      "functions": [
        {
          "filter": { "match": { "test": "bar" } },
          "random_score": {},
          "weight": 23
        },
        {
          "filter": { "match": { "test": "cat" } },
          "weight": 42
        }
      ],
      "max_boost": 42,
      "score_mode": "max",
      "boost_mode": "multiply",
      "min_score": 42
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $functionScore) {
            $functionScore->query(function (Query $q) {
                $q->matchAll();
            })->boost("5")
                ->maxBoost(42)
                ->functions([
                    ['filter' => (new Query())->match('test', 'bar'), 'random_score' => new RandomScore(), 'weight' => 23],
                    ['filter' => (new Query())->match('test', 'cat'), 'weight' => 42],
                ])
            ->maxBoost(42)
            ->scoreMode('max')
            ->boostMode('multiply')
            ->minScore(42);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testScriptScore()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "query": {
        "match": { "message": "elasticsearch" }
      },
      "functions": [
        {
          "script_score": {
            "script": {
              "source": "Math.log(2 + doc['my-int'].value)"
            }
          }
        }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $functionScore) {
            $functionScore->query(function (Query $q) {
                $q->match('message', 'elasticsearch');
            })->addFunction(function (Function_ $fb) {
                $fb->scriptScore(function (ScriptScore $ss) {
                    $ss->script(function (Script $script) {
                        $script->source('Math.log(2 + doc[\'my-int\'].value)');
                    });
                });
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testFunctions()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "functions": [
        {"field_value_factor": {"field": "my-int","factor": 1.2,"modifier": "sqrt","missing": 1}},
        {"gauss": {"price": {"origin": "0","scale": "20"}}}
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $functionScore) {
            $functionScore->addFunction(function (Function_ $fb) {
                $fb->fieldValueFactor(function (FieldValueFactor $factor) {
                    $factor->field('my-int')->factor(1.2)->modifier('sqrt')->missing(1);
                });
            })->addFunction(function (Function_ $fb) {
                $fb->gauss(function (Gauss $gauss) {
                    $gauss->field('price')->origin("0")->scale("20");
                });
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testExpDecayFunction()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "query": { "match_all": {} },
      "functions": [
        {
          "exp": {
            "price": {
              "origin": 0,
              "scale": "20",
              "offset": 5,
              "decay": 0.5
            }
          }
        }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $fs) {
            $fs->query(function (Query $q) {
                $q->matchAll();
            })->functions([
                (new Exp())->field('price')->origin(0)->scale("20")->offset(5)->decay(0.5)
            ]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testLinearDecayFunction()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "query": { "match_all": {} },
      "functions": [
        {
          "linear": {
            "date": {
              "origin": "2024-01-01",
              "scale": "10d"
            }
          }
        }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $fs) {
            $fs->query(function (Query $q) {
                $q->matchAll();
            })->functions([
                (new Linear())->field('date')->origin("2024-01-01")->scale("10d")
            ]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testFunctionScoreWithBuilder()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "function_score": {
      "query": { "match_all": {} },
      "boost": "5",
      "functions": [
        {
          "filter": { "match": { "test": "bar" } },
          "random_score": {},
          "weight": 23
        },
        {
          "filter": { "match": { "test": "cat" } },
          "weight": 42
        }
      ],
      "max_boost": 42,
      "score_mode": "max",
      "boost_mode": "multiply",
      "min_score": 42
    }
  }
}
JSON;
        $query = new Query();
        $query->functionScore(function (FunctionScore $fs) {
            $fs->query(function (Query $q) {
                $q->matchAll();
            })->boost("5")
            ->addFunction(function (Function_ $fb) {
                $fb->filter(function (Query $q) {
                    $q->match('test', 'bar');
                });
                $fb->randomScore();
                $fb->weight(23);
            })
            ->addFunction(function (Function_ $fb) {
                $fb->filter(function (Query $q) {
                    $q->match('test', 'cat');
                });
                $fb->weight(42);
            })
            ->maxBoost(42)
            ->scoreMode('max')
            ->boostMode('multiply')
            ->minScore(42);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testConstantScore()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "constant_score": {
      "filter": {
        "term": { "status": "active" }
      },
      "boost": 1.2
    }
  }
}
JSON;
        $query = new Query();
        $query->constantScore(function (ConstantScore $constantScore) {
            $constantScore->filter(function (Query $q) {
                $q->term(['status' => 'active']);
            })->boost(1.2);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testConstantScoreArrayWithClosure()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "constant_score": {
      "filter": {
        "term": { "status": "active" }
      },
      "boost": 1.2
    }
  }
}
JSON;
        $query = new Query();
        $query->constantScore([
            'filter' => function (Query $q) {
                $q->term(['status' => 'active']);
            },
            'boost' => 1.2,
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testWhenTrue()
    {
        $query = new Query();
        $query->when(true, function (Query $q) {
            $q->term('status', 'published');
        });
        $this->assertQuery('{"query":{"term":{"status":"published"}}}', $query);
    }

    public function testWhenFalse()
    {
        $query = new Query();
        $query->when(false, function (Query $q) {
            $q->term('status', 'published');
        });
        $this->assertEquals([], $query->toArray());
    }

    public function testWhenFalseWithDefault()
    {
        $query = new Query();
        $query->when(false, function (Query $q) {
            $q->term('status', 'published');
        }, function (Query $q) {
            $q->term('status', 'draft');
        });
        $this->assertQuery('{"query":{"term":{"status":"draft"}}}', $query);
    }

    public function testWhenCallableCondition()
    {
        $status = 'published';
        $query = new Query();
        $query->when(function () use ($status) {
            return $status === 'published';
        }, function (Query $q) {
            $q->term('status', 'published');
        });
        $this->assertQuery('{"query":{"term":{"status":"published"}}}', $query);
    }

    public function testWhenChaining()
    {
        $query = new Query();
        $query
            ->match('title', 'elasticsearch')
            ->when(true, function (Query $q) {
                $q->term('status', 'published');
            })
            ->match('content', 'guide');
        $this->assertQuery('{"query":{"match":{"title":"elasticsearch"},"term":{"status":"published"},"match":{"content":"guide"}}}', $query);
    }

    public function testWhenWithArrayQuery()
    {
        $query = new Query();
        $query->when(true, ['term' => ['status' => 'published']]);
        $this->assertQuery('{"query":{"term":{"status":"published"}}}', $query);
    }

    public function testBoolAddMustWithNode()
    {
        $query = new Query();
        $query->bool(function (Boolean $b) {
            $b->must(new \ElasticKit\DSL\Queries\FullText\Match_('title', 'test'));
            $b->filter(new \ElasticKit\DSL\Queries\TermLevel\Term('status', 'published'));
        });
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        { "match": { "title": "test" } }
      ],
      "filter": [
        { "term": { "status": "published" } }
      ]
    }
  }
}
JSON;
        $this->assertQuery($expectedJson, $query);
    }

    public function testBoolDynamicBuild()
    {
        $filters = ['brand' => 'nike', 'color' => 'red'];
        $query = new Query();
        $query->bool(function (Boolean $b) use ($filters) {
            $b->must(function (Query $q) {
                $q->match('title', 'shoes');
            });
            foreach ($filters as $field => $value) {
                $b->filter(new \ElasticKit\DSL\Queries\TermLevel\Term($field, $value));
            }
        });
        $expectedJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        { "match": { "title": "shoes" } }
      ],
      "filter": [
        { "term": { "brand": "nike" } },
        { "term": { "color": "red" } }
      ]
    }
  }
}
JSON;
        $this->assertQuery($expectedJson, $query);
    }

    public function testDisMaxAddQuery()
    {
        $query = new Query();
        $query->disMax(function (DisjunctionMax $dm) {
            $dm->queries(function (Query $q) {
                $q->term('title', 'Quick pets');
            });
            $dm->queries(function (Query $q) {
                $q->term('body', 'Quick pets');
            });
            $dm->tieBreaker(0.7);
        });
        $expectedJson = <<<JSON
{
  "query": {
    "dis_max": {
      "queries": [
        { "term": { "title": "Quick pets" } },
        { "term": { "body": "Quick pets" } }
      ],
      "tie_breaker": 0.7
    }
  }
}
JSON;
        $this->assertQuery($expectedJson, $query);
    }
}

<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\FullText\CombinedFields;
use ElasticKit\DSL\Queries\FullText\Intervals;
use ElasticKit\DSL\Queries\FullText\MatchBoolPrefix;
use ElasticKit\DSL\Queries\FullText\MatchPhrasePrefix;
use ElasticKit\DSL\Queries\FullText\MultiMatch;
use ElasticKit\DSL\Queries\FullText\Match_;
use ElasticKit\DSL\Queries\FullText\QueryString;
use ElasticKit\DSL\Queries\FullText\SimpleQueryString;
use ElasticKit\DSL\Queries\Script;

class FullTextQueriesTest extends DslTestCase
{
    public function testIntervals()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals" : {
      "my_text" : {
        "all_of" : {
          "ordered" : true,
          "intervals" : [
            {
              "match" : {
                "query" : "my favorite food",
                "max_gaps" : 0,
                "ordered" : true
              }
            },
            {
              "any_of" : {
                "intervals" : [
                  { "match" : { "query" : "hot water" } },
                  { "match" : { "query" : "cold porridge" } }
                ]
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
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->allOf(function (Intervals\AllOf $allOf) {
                $allOf->ordered(true);
                $allOf->intervals(function (Intervals $intervals) {
                    $intervals->match(function (Intervals\Match_ $match) {
                        $match->query('my favorite food')->maxGaps(0)->ordered(true);
                    });
                    $intervals->anyOf(function (Intervals\AnyOf $anyOf) {
                        $anyOf->intervals(function (Intervals $intervals) {
                            $intervals->match(['query' => 'hot water']);
                            $intervals->match(['query' => 'cold porridge']);
                        });
                    });
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsPreservesInheritedProperties()
    {
        // 继承自 Node 的 boost() 等方法此前被 Intervals::toArray() 静默丢弃，
        // toArray 不应再绕过 $_properties。
        $exampleJson = <<<JSON
{
  "query": {
    "intervals" : {
      "my_text" : {
        "match" : {
          "query" : "salty"
        },
        "boost" : 2.0
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->match(['query' => 'salty']);
            $intervals->boost(2.0);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsThrowsOnDuplicateRuleKey()
    {
        // 字段节点只接受单个 rule，两个 match 不应静默合并（后者覆盖前者），应抛异常。
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->match(['query' => 'foo']);
            $intervals->match(['query' => 'bar']);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate interval clause key "match"');
        $query->toArray();
    }

    public function testIntervals2()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals" : {
      "my_text" : {
        "match" : {
          "query" : "salty",
          "filter" : {
            "contained_by" : {
              "match" : {
                "query" : "hot porridge"
              }
             },
            "script" : {
                "source" : "interval.start > 10 && interval.end < 20 && interval.gaps == 0"
            }
          }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->match(function (Intervals\Match_ $match) {
                $match->query('salty');
                $match->filter(function (Intervals\Filter $filter) {
                    $filter->containedBy(['match' => ['query' => 'hot porridge']]);
                    $filter->script(function (Script $script) {
                        $script->source('interval.start > 10 && interval.end < 20 && interval.gaps == 0');
                    });
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testMatch()
    {
        $exampleJson = <<<JSON
{
   "query": {
       "match" : {
           "message": {
               "query" : "ny city",
               "auto_generate_synonyms_phrase_query" : false
           }
       }
   }
}
JSON;
        $query = new Query();
        $query->match('message', function (Match_ $match) {
            $match->query('ny city');
            $match->autoGenerateSynonymsPhraseQuery(false);
        });
        $this->assertQuery($exampleJson, $query);
        /*------------------------------------------------------*/
        $exampleJson2 = <<<JSON
{
  "query": {
    "match": {
      "message": "this is a test"
    }
  }
}
JSON;
        $query = new Query();
        $query->match('message', 'this is a test');
        $this->assertQuery($exampleJson2, $query);
    }

    public function testMatchBoolPrefix()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "match_bool_prefix": {
      "message": {
        "query": "quick brown f",
        "analyzer": "keyword"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchBoolPrefix('message', function (MatchBoolPrefix $matchBoolPrefix) {
            $matchBoolPrefix->query('quick brown f');
            $matchBoolPrefix->analyzer('keyword');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testMatchPhrase()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "match_phrase": {
      "message": {
        "query": "this is a test",
        "analyzer": "my_analyzer"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchPhrase('message', ['query' => 'this is a test', 'analyzer' => 'my_analyzer']);
        $this->assertQuery($exampleJson, $query);
        $exampleJson = <<<JSON
{
  "query": {
    "match_phrase_prefix": {
      "message": {
        "query": "quick brown f"
      }
    }
  }
}
JSON;
        $query = new Query();
        $matchPhrasePrefix = new MatchPhrasePrefix();
        $matchPhrasePrefix->field('message')->query('quick brown f');
        $query->matchPhrasePrefix($matchPhrasePrefix);
        $this->assertQuery($exampleJson, $query);
    }

    public function testCombinedFields()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "combined_fields" : {
      "query":      "database systems",
      "fields":     [ "title", "abstract", "body"],
      "operator":   "and"
    }
  }
}
JSON;
        $query = new Query();
        $query->combinedFields([
            'query' => 'database systems',
            'fields' => ['title', 'abstract', 'body'],
            'operator' => 'and'
        ]);
        $this->assertQuery($exampleJson, $query);
    }

    public function testMultiMatch()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "multi_match" : {
      "query":      "quick brown f",
      "type":       "bool_prefix",
      "fields":     [ "subject", "message" ]
    }
  }
}
JSON;
        $query = new Query();
        $query->multiMatch(function (MultiMatch $multiMatch) {
            $multiMatch->query('quick brown f');
            $multiMatch->type('bool_prefix');
            $multiMatch->fields(['subject', 'message']);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testQueryStringCrossFields()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "query_string": {
      "fields": [
        "title",
        "content"
      ],
      "query": "this OR that OR thus",
      "type": "cross_fields",
      "minimum_should_match": 2
    }
  }
}
JSON;
        $query = new Query();
        $query->queryString(function (QueryString $queryString) {
            $queryString->fields(['title', 'content']);
            $queryString->query('this OR that OR thus');
            $queryString->type('cross_fields');
            $queryString->minimumShouldMatch(2);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSimpleQueryString()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "simple_query_string" : {
        "query": "\"fried eggs\" +(eggplant | potato) -frittata",
        "fields": ["title^5", "body"],
        "default_operator": "and"
    }
  }
}
JSON;
        $query = new Query();
        $query->simpleQueryString(function (SimpleQueryString $simpleQueryString) {
            $simpleQueryString->query('"fried eggs" +(eggplant | potato) -frittata');
            $simpleQueryString->fields(['title^5', 'body']);
            $simpleQueryString->defaultOperator('and');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testMatchPhraseWithSlop()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "match_phrase": {
      "title": {
        "query": "quick brown fox",
        "slop": 3
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchPhrase('title', ['query' => 'quick brown fox', 'slop' => 3]);
        $this->assertQuery($exampleJson, $query);
    }

    public function testMatchBoolPrefixWithFuzziness()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "match_bool_prefix": {
      "message": {
        "query": "quick brown f",
        "fuzziness": "AUTO",
        "operator": "and"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchBoolPrefix('message', function (MatchBoolPrefix $matchBoolPrefix) {
            $matchBoolPrefix->query('quick brown f');
            $matchBoolPrefix->fuzziness('AUTO');
            $matchBoolPrefix->operator('and');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testMultiMatchWithFuzziness()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "multi_match": {
      "query": "quick brown f",
      "type": "best_fields",
      "fields": ["title", "body"],
      "fuzziness": "AUTO",
      "lenient": true
    }
  }
}
JSON;
        $query = new Query();
        $query->multiMatch(function (MultiMatch $multiMatch) {
            $multiMatch->query('quick brown f');
            $multiMatch->type('best_fields');
            $multiMatch->fields(['title', 'body']);
            $multiMatch->fuzziness('AUTO');
            $multiMatch->lenient(true);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testQueryStringWithAllowLeadingWildcard()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "query_string": {
      "query": "*elasticsearch",
      "allow_leading_wildcard": true,
      "analyze_wildcard": true
    }
  }
}
JSON;
        $query = new Query();
        $query->queryString(function (QueryString $queryString) {
            $queryString->query('*elasticsearch');
            $queryString->allowLeadingWildcard(true);
            $queryString->analyzeWildcard(true);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testCombinedFieldsWithQuery()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "combined_fields": {
      "query": "database systems",
      "fields": ["title", "abstract"],
      "operator": "or"
    }
  }
}
JSON;
        $query = new Query();
        $query->combinedFields(function (CombinedFields $combinedFields) {
            $combinedFields->query('database systems');
            $combinedFields->fields(['title', 'abstract']);
            $combinedFields->operator('or');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSimpleQueryStringWithBoost()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "simple_query_string": {
      "query": "foo bar",
      "fields": ["content"],
      "boost": 2.0
    }
  }
}
JSON;
        $query = new Query();
        $query->simpleQueryString(function (SimpleQueryString $simpleQueryString) {
            $simpleQueryString->query('foo bar');
            $simpleQueryString->fields(['content']);
            $simpleQueryString->boost(2.0);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsFuzzy()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "fuzzy": {
          "term": "foo",
          "prefix_length": 1,
          "fuzziness": "AUTO",
          "transpositions": true
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->fuzzy(function (Intervals\Fuzzy $f) {
                $f->term('foo')->prefixLength(1)->fuzziness('AUTO')->transpositions(true);
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsPrefix()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "prefix": {
          "prefix": "ela",
          "analyzer": "standard"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->prefix(function (Intervals\Prefix $p) {
                $p->prefix('ela')->analyzer('standard');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsWildcard()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "wildcard": {
          "pattern": "ela*"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->wildcard(function (Intervals\Wildcard $w) {
                $w->pattern('ela*');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsRegexp()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "regexp": {
          "pattern": "h[ao]t"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->regexp(function (Intervals\Regexp $r) {
                $r->pattern('h[ao]t');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testIntervalsRange()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "range": {
          "gte": "a",
          "lte": "z"
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->range(function (Intervals\Range $r) {
                $r->gte('a')->lte('z');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testAllOfAddInterval()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals" : {
      "my_text" : {
        "all_of" : {
          "intervals" : [
            { "match" : { "query" : "hot water" } },
            { "match" : { "query" : "cold porridge" } }
          ]
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->allOf(function (Intervals\AllOf $allOf) {
                $allOf->addInterval(function (Intervals $i) {
                    $i->match(['query' => 'hot water']);
                });
                $allOf->addInterval(function (Intervals $i) {
                    $i->match(['query' => 'cold porridge']);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testAnyOfAddInterval()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "intervals" : {
      "my_text" : {
        "any_of" : {
          "intervals" : [
            { "match" : { "query" : "hot water" } },
            { "match" : { "query" : "cold porridge" } }
          ]
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->anyOf(function (Intervals\AnyOf $anyOf) {
                $anyOf->addInterval(function (Intervals $i) {
                    $i->match(['query' => 'hot water']);
                });
                $anyOf->addInterval(function (Intervals $i) {
                    $i->match(['query' => 'cold porridge']);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testAllOfFilter()
    {
        // all_of::filter() must wrap in a Filter rule like any_of::filter()
        $exampleJson = <<<JSON
{
  "query": {
    "intervals": {
      "my_text": {
        "all_of": {
          "intervals": [
            { "match": { "query": "hot water" } }
          ],
          "filter": {
            "after": { "match": { "query": "cold porridge" } }
          }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->intervals('my_text', function (Intervals $intervals) {
            $intervals->allOf(function (Intervals\AllOf $allOf) {
                $allOf->addInterval(function (Intervals $i) {
                    $i->match(['query' => 'hot water']);
                });
                $allOf->filter(function (Intervals\Filter $filter) {
                    $filter->after(['match' => ['query' => 'cold porridge']]);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }
}

<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Span\SpanContaining;
use ElasticKit\DSL\Queries\Span\SpanFieldMasking;
use ElasticKit\DSL\Queries\Span\SpanFirst;
use ElasticKit\DSL\Queries\Span\SpanMulti;
use ElasticKit\DSL\Queries\Span\SpanNear;
use ElasticKit\DSL\Queries\Span\SpanNot;
use ElasticKit\DSL\Queries\Span\SpanOr;
use ElasticKit\DSL\Queries\Span\SpanTerm;
use ElasticKit\DSL\Queries\Span\SpanWithin;
use ElasticKit\DSL\Queries\TermLevel\Prefix;

class SpanQueriesTest extends DslTestCase
{
    public function testSpanContaining()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_containing": {
      "little": {
        "span_term": { "field1": "foo" }
      },
      "big": {
        "span_near": {
          "clauses": [
            { "span_term": { "field1": "bar" } },
            { "span_term": { "field1": "baz" } }
          ],
          "slop": 5,
          "in_order": true
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->spanContaining(function (SpanContaining $containing) {
            $containing->little(function (Query $query) {
                $query->spanTerm('field1', 'foo');
            });
            $containing->big(function (Query $query) {
                $query->spanNear(function (SpanNear $spanNear) {
                    $spanNear->clauses(function (Query $query) {
                        $query->spanTerm('field1', 'bar');
                        $query->spanTerm('field1', 'baz');
                    })->slop(5)->inOrder(true);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanFieldMasking()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_near": {
      "clauses": [
        {
          "span_term": {
            "text": "quick brown"
          }
        },
        {
          "span_field_masking": {
            "query": {
              "span_term": {
                "text.stems": "fox"
              }
            },
            "field": "text"
          }
        }
      ],
      "slop": 5,
      "in_order": false
    }
  }
}
JSON;
        $query = new Query();
        $query->spanNear(function (SpanNear $spanNear) {
            $spanNear->clauses(function (Query $query) {
                $query->spanTerm('text', 'quick brown');
                $query->spanFieldMasking(function (SpanFieldMasking $fieldMasking) {
                    $fieldMasking->query(function (Query $query) {
                        $query->spanTerm('text.stems', 'fox');
                    });
                    $fieldMasking->field('text');
                });
            })->slop(5)->inOrder(false);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanFirst()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_first": {
      "match": {
        "span_term": { "user.id": "kimchy" }
      },
      "end": 3
    }
  }
}
JSON;
        $query = new Query();
        $query->spanFirst(function (SpanFirst $spanFirst) {
            $spanFirst->match(function (Query $query) {
                $query->spanTerm('user.id', 'kimchy');
            })->end(3);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanMulti()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_multi": {
      "match": {
        "prefix": { "user.id": { "value": "ki", "boost": 1.08 } }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->spanMulti(function (SpanMulti $spanMulti) {
            $spanMulti->match(function (Query $query) {
                $query->prefix('user.id', function (Prefix $prefix) {
                    $prefix->value('ki')->boost(1.08);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanNear()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_near": {
      "clauses": [
        { "span_term": { "field": "value1" } },
        { "span_term": { "field": "value2" } },
        { "span_term": { "field": "value3" } }
      ],
      "slop": 12,
      "in_order": false
    }
  }
}
JSON;
        $query = new Query();
        $query->spanNear(function (SpanNear $spanNear) {
            $spanNear->clauses(function (Query $query) {
                $query->spanTerm('field', 'value1');
                $query->spanTerm('field', 'value2');
                $query->spanTerm('field', 'value3');
            })->slop(12)->inOrder(false);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanNot()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_not": {
      "include": {
        "span_term": { "field1": "hoya" }
      },
      "exclude": {
        "span_near": {
          "clauses": [
            { "span_term": { "field1": "la" } },
            { "span_term": { "field1": "hoya" } }
          ],
          "slop": 0,
          "in_order": true
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->spanNot(function (SpanNot $spanNot) {
            $spanNot->include(function (Query $query) {
                $query->spanTerm('field1', 'hoya');
            });
            $spanNot->exclude(function (Query $query) {
                $query->spanNear(function (SpanNear $spanNear) {
                    $spanNear->clauses(function (Query $query) {
                        $query->spanTerm('field1', 'la');
                        $query->spanTerm('field1', 'hoya');
                    })->slop(0)->inOrder(true);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanOr()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_or" : {
      "clauses" : [
        { "span_term" : { "field" : "value1" } },
        { "span_term" : { "field" : "value2" } },
        { "span_term" : { "field" : "value3" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->spanOr(function (SpanOr $spanOr) {
            $spanOr->clauses(function (Query $query) {
                $query->spanTerm('field', 'value1');
                $query->spanTerm('field', 'value2');
                $query->spanTerm('field', 'value3');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanTerm()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_term" : { "user.id" : { "term" : "kimchy", "boost" : 2.0 } }
  }
}
JSON;
        $query = new Query();
        $query->spanTerm('user.id', function (SpanTerm $spanTerm) {
            $spanTerm->term('kimchy')->boost(2.0);
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanWithin()
    {
        $exampleJson = <<<'JSON'
{
  "query": {
    "span_within": {
      "little": {
        "span_term": { "field1": "foo" }
      },
      "big": {
        "span_near": {
          "clauses": [
            { "span_term": { "field1": "bar" } },
            { "span_term": { "field1": "baz" } }
          ],
          "slop": 5,
          "in_order": true
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->spanWithin(function (SpanWithin $spanWithin) {
            $spanWithin->little(function (Query $query) {
                $query->spanTerm('field1', 'foo');
            });
            $spanWithin->big(function (Query $query) {
                $query->spanNear(function (SpanNear $spanNear) {
                    $spanNear->clauses(function (Query $query) {
                        $query->spanTerm('field1', 'bar');
                        $query->spanTerm('field1', 'baz');
                    })->slop(5)->inOrder(true);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanOrAddClause()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "span_or": {
      "clauses": [
        { "span_term": { "field1": "bar" } },
        { "span_term": { "field2": "baz" } }
      ]
    }
  }
}
JSON;
        $query = new Query();
        $query->spanOr(function (SpanOr $spanOr) {
            $spanOr->clauses(function (Query $q) {
                $q->spanTerm('field1', 'bar');
            });
            $spanOr->clauses(function (Query $q) {
                $q->spanTerm('field2', 'baz');
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testSpanNearAddClause()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "span_near": {
      "clauses": [
        { "span_term": { "field1": "bar" } },
        { "span_term": { "field2": "baz" } }
      ],
      "slop": 5,
      "in_order": true
    }
  }
}
JSON;
        $query = new Query();
        $query->spanNear(function (SpanNear $spanNear) {
            $spanNear->clauses(function (Query $q) {
                $q->spanTerm('field1', 'bar');
            });
            $spanNear->clauses(function (Query $q) {
                $q->spanTerm('field2', 'baz');
            });
            $spanNear->slop(5)->inOrder(true);
        });
        $this->assertQuery($exampleJson, $query);
    }

}

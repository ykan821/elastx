<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Fuzzy;
use ElasticKit\DSL\Queries\TermLevel\Prefix;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Regexp;
use ElasticKit\DSL\Queries\TermLevel\TermsSet;
use ElasticKit\DSL\Queries\TermLevel\Wildcard;

class TermLevelTest extends DslTestCase
{
    public function testExists()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "exists": {
      "field": "user"
    }
  }
}
JSON;
        $query = new Query();
        $query->exists('user');
        $this->assertQuery($exampleJson, $query);
    }

    public function testFuzzy()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "fuzzy": {
      "user.id": {
        "value": "ki",
        "fuzziness": "AUTO",
        "max_expansions": 50,
        "prefix_length": 0,
        "transpositions": true,
        "rewrite": "constant_score_blended"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->fuzzy('user.id', function (Fuzzy $fuzzy) {
            $fuzzy->value('ki');
            $fuzzy->fuzziness('AUTO');
            $fuzzy->maxExpansions(50);
            $fuzzy->prefixLength(0);
            $fuzzy->transpositions(true);
            $fuzzy->rewrite('constant_score_blended');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testTerm()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "term": {
      "status": {
        "value": "published",
        "boost": 2.0
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->term('status', ['value' => 'published', 'boost' => 2.0]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTermSimple()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "term": { "status": "published" }
  }
}
JSON;
        $query = new Query();
        $query->term('status', 'published');
        $this->assertQuery($expectedJson, $query);
    }

    public function testTerms()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "terms": {
      "status": ["pending", "published"]
    }
  }
}
JSON;
        $query = new Query();
        $query->terms('status', ['pending', 'published']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testRange()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "range": {
      "age": {
        "gte": 10,
        "lte": 20
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->range('age', function (Range $r) {
            $r->gte(10)->lte(20);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testRangeShorthand()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "range": {
      "age": {
        "gte": 10,
        "lte": 20
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->range('age', [10, 20]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testRangeOperators()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "range": {
      "price": {
        "gt": 100,
        "lt": 500
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->range('price', ['>' => 100, '<' => 500]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testPrefix()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "prefix": {
      "user.id": {
        "value": "ki"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->prefix('user.id', function (Prefix $p) {
            $p->value('ki');
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testRegexp()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "regexp": {
      "title": {
        "value": "k.*",
        "flags": "COMPLEMENT"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->regexp('title', function (Regexp $r) {
            $r->value('k.*')->flags('COMPLEMENT');
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testWildcard()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "wildcard": {
      "user.id": {
        "value": "ki*y"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->wildcard('user.id', function (Wildcard $w) {
            $w->value('ki*y');
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testIds()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "ids": {
      "values": ["1", "2", "3"]
    }
  }
}
JSON;
        $query = new Query();
        $query->ids(['1', '2', '3']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTermsSet()
    {
        $expectedJson = <<<JSON
{
  "query": {
    "terms_set": {
      "programming_languages": {
        "terms": ["c++", "java", "php"],
        "minimum_should_match": 2
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->termsSet('programming_languages', function (TermsSet $ts) {
            $ts->terms(['c++', 'java', 'php']);
            $ts->minimumShouldMatch(2);
        });
        $this->assertQuery($expectedJson, $query);
    }
}

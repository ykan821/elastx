<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;

class ParamsTest extends DslTestCase
{
    public function testSizeWithQuery()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match": { "title": "test" }
  },
  "size": 10
}
JSON;
        $query = new Query();
        $query->match('title', 'test');
        $query->size(10);
        $this->assertQuery($expectedJson, $query);
    }

    public function testFrom()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "from": 20
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->from(20);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTimeout()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "timeout": "5s"
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->timeout('5s');
        $this->assertQuery($expectedJson, $query);
    }

    public function testMinScore()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "min_score": 0.5
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->minScore(0.5);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTerminateAfter()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "terminate_after": 1000
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->terminateAfter(1000);
        $this->assertQuery($expectedJson, $query);
    }

    public function testExplain()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "explain": true
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->explain(true);
        $this->assertQuery($expectedJson, $query);
    }

    public function testVersion()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "version": true
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->version(true);
        $this->assertQuery($expectedJson, $query);
    }

    public function testProfile()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "profile": true
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->profile(true);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTrackTotalHits()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "track_total_hits": false
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->trackTotalHits(false);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSeqNoPrimaryTerm()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "seq_no_primary_term": true
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->seqNoPrimaryTerm(true);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSort()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "sort": [
    { "created_at": "desc" }
  ]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->sort([['created_at' => 'desc']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSortChained()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "sort": [
    { "status": "asc" },
    { "price": "desc" }
  ]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->sort('status', 'asc');
        $query->sort('price', 'desc');
        $this->assertQuery($expectedJson, $query);
    }

    public function testSortFieldWithoutOrder()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "sort": [
    "_score"
  ]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->sort('_score');
        $this->assertQuery($expectedJson, $query);
    }

    public function testSource()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "_source": ["title", "content"]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->source(['title', 'content']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSourceFalse()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "_source": false
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->source(false);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSearchAfter()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "search_after": [1463538857, "tweet#654323"]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->searchAfter([1463538857, 'tweet#654323']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testStoredFields()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "stored_fields": ["title", "content"]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->storedFields(['title', 'content']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testDocvalueFields()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "docvalue_fields": ["price"]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->docvalueFields(['price']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testIndicesBoost()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "indices_boost": [{ "index-1": 1.4 }]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->indicesBoost(['index-1' => 1.4]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testIndicesBoostChained()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "indices_boost": [
    { "index-1": 1.4 },
    { "index-2": 1.2 }
  ]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->indicesBoost(['index-1' => 1.4])
              ->indicesBoost(['index-2' => 1.2]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testChainingParamsAndQuery()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match": { "title": "test" },
    "term": { "status": "published" }
  },
  "size": 10,
  "from": 20,
  "sort": [
    { "created_at": "desc" }
  ]
}
JSON;
        $query = new Query();
        $query->match('title', 'test')
              ->term('status', 'published')
              ->size(10)
              ->from(20)
              ->sort([['created_at' => 'desc']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testParamsWithAggs()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "size": 0,
  "aggs": {
    "categories": {
      "terms": { "field": "category", "size": 20 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->size(0);
        $query->aggs('categories', function ($a) {
            $a->terms(function (\ElasticKit\DSL\Aggs\Bucket\Terms $t) {
                $t->field('category')->size(20);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testTrackScores()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "track_scores": true
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->trackScores(true);
        $this->assertQuery($expectedJson, $query);
    }

    public function testFields()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "fields": ["title", "content"]
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->fields(['title', 'content']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testPit()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "pit": {
    "id": "some-base64-id",
    "keep_alive": "1m"
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->pit(['id' => 'some-base64-id', 'keep_alive' => '1m']);
        $this->assertQuery($expectedJson, $query);
    }

    public function testScriptFields()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "script_fields": {
    "price_with_tax": {
      "script": {
        "source": "doc['price'].value * 1.2"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->scriptFields([
            'price_with_tax' => [
                'script' => ['source' => "doc['price'].value * 1.2"]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testRuntimeMappings()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "runtime_mappings": {
    "day_of_week": {
      "type": "keyword",
      "script": {
        "source": "emit(doc.timestamp.value)"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->runtimeMappings([
            'day_of_week' => [
                'type' => 'keyword',
                'script' => ['source' => 'emit(doc.timestamp.value)']
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testParamsDoNotLeakIntoQuery()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match": { "title": "test" }
  },
  "size": 10,
  "from": 0
}
JSON;
        $query = new Query();
        $query->match('title', 'test');
        $query->size(10);
        $query->from(0);
        $this->assertQuery($expectedJson, $query);
    }
}

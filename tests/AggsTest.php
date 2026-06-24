<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Aggs\Bucket\Terms;
use ElasticKit\DSL\Aggs\Bucket\Range;
use ElasticKit\DSL\Aggs\Bucket\GeoDistance;

class AggsTest extends DslTestCase
{
    public function testTermsregation()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match": { "title": "elasticsearch" }
  },
  "aggs": {
    "by_status": {
      "terms": { "field": "status", "size": 10 }
    }
  }
}
JSON;
        $query = new Query();
        $query->match('title', 'elasticsearch');
        $query->aggs('by_status', function ($a) {
            $a->terms(function (Terms $t) {
                $t->field('status')->size(10);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testNestedAggregation()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match": { "title": "elasticsearch" }
  },
  "aggs": {
    "by_status": {
      "terms": { "field": "status", "size": 10 },
      "aggs": {
        "avg_price": {
          "avg": { "field": "price" }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->match('title', 'elasticsearch');
        $query->aggs('by_status', function ($a) {
            $a->terms(function (Terms $t) {
                $t->field('status')->size(10);
            });
            $a->aggs('avg_price', ['avg' => ['field' => 'price']]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testFilterAggregation()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "aggs": {
    "t_shirts": {
      "filter": { "term": { "type": "t-shirt" } }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('t_shirts', function ($a) {
            $a->filter(function (Query $q) {
                $q->term('type', 't-shirt');
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testDeepNestedAggregation()
    {
$expectedJson = <<<JSON
{
  "query": {
    "match_all": {}
  },
  "aggs": {
    "by_status": {
      "terms": { "field": "status" },
      "aggs": {
        "by_date": {
          "date_histogram": { "field": "created", "calendar_interval": "month" },
          "aggs": {
            "avg_price": {
              "avg": { "field": "price" }
            }
          }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_status', function ($a) {
            $a->terms(['field' => 'status']);
            $a->aggs('by_date', function ($a) {
                $a->dateHistogram(['field' => 'created', 'calendar_interval' => 'month']);
                $a->aggs('avg_price', ['avg' => ['field' => 'price']]);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testRangeAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "price_ranges": {
      "range": {
        "field": "price",
        "ranges": [
          { "to": 100 },
          { "from": 100, "to": 500 },
          { "from": 500 }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('price_ranges', function ($a) {
            $a->range(function (Range $r) {
                $r->field('price')->ranges([["to" => 100], ["from" => 100, "to" => 500], ["from" => 500]]);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testHistogramAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "prices": {
      "histogram": {
        "field": "price",
        "interval": 50
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('prices', ['histogram' => ['field' => 'price', 'interval' => 50]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testNestedAggType()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "comments": {
      "nested": { "path": "comments" },
      "aggs": {
        "top_authors": {
          "terms": { "field": "comments.author" }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('comments', function ($a) {
            $a->nested(['path' => 'comments']);
            $a->aggs('top_authors', ['terms' => ['field' => 'comments.author']]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testMetricAggregations()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "avg_price": { "avg": { "field": "price" } },
    "max_price": { "max": { "field": "price" } },
    "total_sales": { "sum": { "field": "sales" } },
    "product_count": { "cardinality": { "field": "product_id" } }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('avg_price', ['avg' => ['field' => 'price']]);
        $query->aggs('max_price', ['max' => ['field' => 'price']]);
        $query->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        $query->aggs('product_count', ['cardinality' => ['field' => 'product_id']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testPipelineAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_date": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } },
        "sales_deriv": {
          "derivative": { "buckets_path": "total_sales" }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
            $a->aggs('sales_deriv', ['derivative' => ['buckets_path' => 'total_sales']]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testGeoDistanceAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "rings": {
      "geo_distance": {
        "field": "location",
        "origin": { "lat": 52.376, "lon": 4.894 },
        "ranges": [
          { "to": 100 },
          { "from": 100, "to": 300 },
          { "from": 300 }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('rings', function ($a) {
            $a->geoDistance(function (GeoDistance $g) {
                $g->field('location')
                  ->origin(['lat' => 52.376, 'lon' => 4.894])
                  ->ranges([["to" => 100], ["from" => 100, "to" => 300], ["from" => 300]]);
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testTermsAggregationWithIncludeExclude()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "tags": {
      "terms": {
        "field": "color",
        "size": 10,
        "include": "red.*",
        "exclude": "blue.*",
        "execution_hint": "map"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('tags', function ($a) {
            $a->terms(function (Terms $t) {
                $t->field('color')->size(10)->include('red.*')->exclude('blue.*')->executionHint('map');
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testHistogramWithOffset()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "prices": {
      "histogram": {
        "field": "price",
        "interval": 50,
        "offset": 25
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('prices', ['histogram' => ['field' => 'price', 'interval' => 50, 'offset' => 25]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testMinAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "min_price": { "min": { "field": "price" } }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('min_price', ['min' => ['field' => 'price']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testValueCountAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "types_count": { "value_count": { "field": "type" } }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('types_count', ['value_count' => ['field' => 'type']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testStatsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "grades_stats": { "stats": { "field": "grade" } }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('grades_stats', ['stats' => ['field' => 'grade']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testAvgBucketPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } }
      }
    },
    "avg_monthly_sales": {
      "avg_bucket": { "buckets_path": "sales_per_month>total_sales" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        });
        $query->aggs('avg_monthly_sales', ['avg_bucket' => ['buckets_path' => 'sales_per_month>total_sales']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSumBucketPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } }
      }
    },
    "sum_monthly_sales": {
      "sum_bucket": { "buckets_path": "sales_per_month>total_sales" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        });
        $query->aggs('sum_monthly_sales', ['sum_bucket' => ['buckets_path' => 'sales_per_month>total_sales']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testBucketScriptPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } },
        "t_shirt_sales": { "sum": { "field": "t_shirt_sales" } },
        "t_shirt_percentage": {
          "bucket_script": {
            "buckets_path": {
              "tShirtSales": "t_shirt_sales",
              "totalSales": "total_sales"
            },
            "script": "params.tShirtSales / params.totalSales * 100"
          }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
            $a->aggs('t_shirt_sales', ['sum' => ['field' => 't_shirt_sales']]);
            $a->aggs('t_shirt_percentage', [
                'bucket_script' => [
                    'buckets_path' => [
                        'tShirtSales' => 't_shirt_sales',
                        'totalSales' => 'total_sales'
                    ],
                    'script' => 'params.tShirtSales / params.totalSales * 100'
                ]
            ]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    // === Missing Bucket Aggregations ===

    public function testAdjacencyMatrixAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "interactions": {
      "adjacency_matrix": {
        "filters": {
          "grpA": { "terms": { "accounts": ["hillary", "sidney"] } },
          "grpB": { "terms": { "accounts": ["donald", "mitt"] } }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('interactions', [
            'adjacency_matrix' => [
                'filters' => [
                    'grpA' => ['terms' => ['accounts' => ['hillary', 'sidney']]],
                    'grpB' => ['terms' => ['accounts' => ['donald', 'mitt']]]
                ]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testAutoDateHistogramAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_date": {
      "auto_date_histogram": { "field": "date", "buckets": 10 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', ['auto_date_histogram' => ['field' => 'date', 'buckets' => 10]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testCategorizeTextAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "categories": {
      "categorize_text": { "field": "message", "size": 10 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('categories', ['categorize_text' => ['field' => 'message', 'size' => 10]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testCompositeAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "products": {
      "composite": {
        "sources": [
          { "product": { "terms": { "field": "product" } } }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('products', [
            'composite' => [
                'sources' => [
                    ['product' => ['terms' => ['field' => 'product']]]
                ]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testDateRangeAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_date": {
      "date_range": {
        "field": "created",
        "ranges": [
          { "from": "now-1y", "to": "now" }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', [
            'date_range' => [
                'field' => 'created',
                'ranges' => [['from' => 'now-1y', 'to' => 'now']]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testDiversifiedSamplerAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sample": {
      "diversified_sampler": { "shard_size": 200, "field": "author" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sample', ['diversified_sampler' => ['shard_size' => 200, 'field' => 'author']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testFiltersAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "messages": {
      "filters": {
        "filters": {
          "errors": { "match": { "body": "error" } },
          "warnings": { "match": { "body": "warning" } }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('messages', [
            'filters' => [
                'filters' => [
                    'errors' => ['match' => ['body' => 'error']],
                    'warnings' => ['match' => ['body' => 'warning']]
                ]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testFrequentItemSetsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "frequent": {
      "frequent_item_sets": {
        "fields": ["items"],
        "minimum_support": 0.1
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('frequent', [
            'frequent_item_sets' => [
                'fields' => ['items'],
                'minimum_support' => 0.1
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testGeoHashGridAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "geohash": {
      "geohash_grid": { "field": "location", "precision": 5 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('geohash', ['geohash_grid' => ['field' => 'location', 'precision' => 5]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testGeohexGridAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "geohex": {
      "geohex_grid": { "field": "location", "precision": 5 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('geohex', ['geohex_grid' => ['field' => 'location', 'precision' => 5]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testGeotileGridAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "geotile": {
      "geotile_grid": { "field": "location", "precision": 5 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('geotile', ['geotile_grid' => ['field' => 'location', 'precision' => 5]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testGlobalAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "all": {
      "global": {}
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('all', function ($a) {
            $a->global();
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testIpPrefixAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "ip_prefixes": {
      "ip_prefix": { "field": "ip", "prefix_length": 24 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('ip_prefixes', ['ip_prefix' => ['field' => 'ip', 'prefix_length' => 24]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testIpRangeAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "ip_ranges": {
      "ip_range": {
        "field": "ip",
        "ranges": [
          { "to": "10.0.0.5" },
          { "from": "10.0.0.5" }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('ip_ranges', [
            'ip_range' => [
                'field' => 'ip',
                'ranges' => [['to' => '10.0.0.5'], ['from' => '10.0.0.5']]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testMissingAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "without_price": {
      "missing": { "field": "price" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('without_price', ['missing' => ['field' => 'price']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testMultiTermsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_color_size": {
      "multi_terms": {
        "terms": [
          { "field": "color" },
          { "field": "size" }
        ]
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_color_size', [
            'multi_terms' => [
                'terms' => [['field' => 'color'], ['field' => 'size']]
            ]
        ]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testParentAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "to_book": {
      "parent": { "type": "book" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('to_book', ['parent' => ['type' => 'book']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testRandomSamplerAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sample": {
      "random_sampler": { "probability": 0.1 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sample', ['random_sampler' => ['probability' => 0.1]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testRareTermsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "rare_authors": {
      "rare_terms": { "field": "author", "max_doc_count": 1 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('rare_authors', ['rare_terms' => ['field' => 'author', 'max_doc_count' => 1]]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testReverseNestedAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "comments": {
      "nested": { "path": "comments" },
      "aggs": {
        "top_users": {
          "reverse_nested": {}
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('comments', function ($a) {
            $a->nested(['path' => 'comments']);
            $a->aggs('top_users', function ($a) {
                $a->reverseNested();
            });
        });
        $this->assertQuery($expectedJson, $query);
    }

    public function testSignificantTermsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "significant": {
      "significant_terms": { "field": "author" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('significant', ['significant_terms' => ['field' => 'author']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testSignificantTextAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "significant": {
      "significant_text": { "field": "content" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('significant', ['significant_text' => ['field' => 'content']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testTimeSeriesAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_time": {
      "time_series": { "field": "timestamp", "interval": "1d" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_time', ['time_series' => ['field' => 'timestamp', 'interval' => '1d']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testVariableWidthHistogramAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "prices": {
      "variable_width_histogram": { "field": "price", "buckets": 10 }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('prices', ['variable_width_histogram' => ['field' => 'price', 'buckets' => 10]]);
        $this->assertQuery($expectedJson, $query);
    }

    // === Missing Metric Aggregation ===

    public function testExtendedStatsAggregation()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "price_stats": { "extended_stats": { "field": "price" } }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('price_stats', ['extended_stats' => ['field' => 'price']]);
        $this->assertQuery($expectedJson, $query);
    }

    // === Missing Pipeline Aggregations ===

    public function testMaxBucketPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } }
      }
    },
    "max_monthly_sales": {
      "max_bucket": { "buckets_path": "sales_per_month>total_sales" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        });
        $query->aggs('max_monthly_sales', ['max_bucket' => ['buckets_path' => 'sales_per_month>total_sales']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testMinBucketPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } }
      }
    },
    "min_monthly_sales": {
      "min_bucket": { "buckets_path": "sales_per_month>total_sales" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        });
        $query->aggs('min_monthly_sales', ['min_bucket' => ['buckets_path' => 'sales_per_month>total_sales']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testStatsBucketPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "sales_per_month": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } }
      }
    },
    "stats_monthly_sales": {
      "stats_bucket": { "buckets_path": "sales_per_month>total_sales" }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('sales_per_month', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
        });
        $query->aggs('stats_monthly_sales', ['stats_bucket' => ['buckets_path' => 'sales_per_month>total_sales']]);
        $this->assertQuery($expectedJson, $query);
    }

    public function testCumulativeSumPipeline()
    {
$expectedJson = <<<JSON
{
  "query": { "match_all": {} },
  "aggs": {
    "by_date": {
      "date_histogram": { "field": "date", "calendar_interval": "month" },
      "aggs": {
        "total_sales": { "sum": { "field": "sales" } },
        "cumulative": {
          "cumulative_sum": { "buckets_path": "total_sales" }
        }
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', function ($a) {
            $a->dateHistogram(['field' => 'date', 'calendar_interval' => 'month']);
            $a->aggs('total_sales', ['sum' => ['field' => 'sales']]);
            $a->aggs('cumulative', ['cumulative_sum' => ['buckets_path' => 'total_sales']]);
        });
        $this->assertQuery($expectedJson, $query);
    }

    // === String shorthand tests ===

    public function testTermsStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_status', ['terms' => ['field' => 'status']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"by_status":{"terms":{"field":"status"}}}}', $query);
    }

    public function testHistogramStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('prices', ['histogram' => ['field' => 'price']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"prices":{"histogram":{"field":"price"}}}}', $query);
    }

    public function testDateHistogramStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', ['date_histogram' => ['field' => 'created']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"by_date":{"date_histogram":{"field":"created"}}}}', $query);
    }

    public function testMissingStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('no_price', ['missing' => ['field' => 'price']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"no_price":{"missing":{"field":"price"}}}}', $query);
    }

    public function testRareTermsStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('rare', ['rare_terms' => ['field' => 'author']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"rare":{"rare_terms":{"field":"author"}}}}', $query);
    }

    public function testSignificantTermsStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('sig', ['significant_terms' => ['field' => 'author']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"sig":{"significant_terms":{"field":"author"}}}}', $query);
    }

    public function testGeoHashGridStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('geohash', ['geohash_grid' => ['field' => 'location']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"geohash":{"geohash_grid":{"field":"location"}}}}', $query);
    }

    public function testRangeStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('price_ranges', ['range' => ['field' => 'price']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"price_ranges":{"range":{"field":"price"}}}}', $query);
    }

    public function testDateRangeStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', ['date_range' => ['field' => 'created']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"by_date":{"date_range":{"field":"created"}}}}', $query);
    }

    public function testVariableWidthHistogramStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('prices', ['variable_width_histogram' => ['field' => 'price']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"prices":{"variable_width_histogram":{"field":"price"}}}}', $query);
    }

    public function testAutoDateHistogramStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('by_date', ['auto_date_histogram' => ['field' => 'date']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"by_date":{"auto_date_histogram":{"field":"date"}}}}', $query);
    }

    public function testGeoDistanceStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('rings', ['geo_distance' => ['field' => 'location']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"rings":{"geo_distance":{"field":"location"}}}}', $query);
    }

    public function testIpPrefixStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('ips', ['ip_prefix' => ['field' => 'ip']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"ips":{"ip_prefix":{"field":"ip"}}}}', $query);
    }

    public function testCategorizeTextStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('cats', ['categorize_text' => ['field' => 'message']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"cats":{"categorize_text":{"field":"message"}}}}', $query);
    }

    public function testGeotileGridStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('geotile', ['geotile_grid' => ['field' => 'location']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"geotile":{"geotile_grid":{"field":"location"}}}}', $query);
    }

    public function testGeohexGridStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('geohex', ['geohex_grid' => ['field' => 'location']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"geohex":{"geohex_grid":{"field":"location"}}}}', $query);
    }

    public function testSignificantTextStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('sig', ['significant_text' => ['field' => 'content']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"sig":{"significant_text":{"field":"content"}}}}', $query);
    }

    // === Pipeline string shorthand tests ===

    public function testAvgBucketStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('avg_sales', ['avg_bucket' => ['buckets_path' => 'monthly>total_sales']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"avg_sales":{"avg_bucket":{"buckets_path":"monthly>total_sales"}}}}', $query);
    }

    public function testDerivativeStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('deriv', ['derivative' => ['buckets_path' => 'total_sales']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"deriv":{"derivative":{"buckets_path":"total_sales"}}}}', $query);
    }

    public function testSumBucketStringShorthand()
    {
        $query = new Query();
        $query->matchAll();
        $query->aggs('total', ['sum_bucket' => ['buckets_path' => 'monthly>sales']]);
        $this->assertQuery('{"query":{"match_all":{}},"aggs":{"total":{"sum_bucket":{"buckets_path":"monthly>sales"}}}}', $query);
    }

    public function testBucketScriptRejectsStringShorthand()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('bucket_script.buckets_path must be a map');

        $query = new Query();
        $query->aggs('script', function ($a) {
            $a->bucketScript('bare_string');
        });
    }
}

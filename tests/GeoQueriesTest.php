<?php


use Tests\DslTestCase;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Compound\Boolean;
use ElasticKit\DSL\Queries\Geo\GeoBoundingBox;
use ElasticKit\DSL\Queries\Geo\GeoDistance;
use ElasticKit\DSL\Queries\Geo\GeoGrid;
use ElasticKit\DSL\Queries\Geo\GeoPolygon;
use ElasticKit\DSL\Queries\Geo\GeoShape;

class GeoQueriesTest extends DslTestCase
{
    public function testGeoBoundingBox()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        {
          "match_all": {}
        }
      ],
      "filter": [
        {
            "geo_bounding_box": {
              "pin.location": {
                "top_left": {
                  "lat": 40.73,
                  "lon": -74.1
                },
                "bottom_right": {
                  "lat": 40.01,
                  "lon": -71.12
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
            $bool->must(function (Query $must) {
                $must->matchAll();
            });
            $bool->filter(function (Query $filter) {
                $filter->geoBoundingBox('pin.location', function (GeoBoundingBox $geoBoundingBox) {
                    $geoBoundingBox->topLeft(['lat' => 40.73, 'lon' => -74.1]);
                    $geoBoundingBox->bottomRight(['lat' => 40.01, 'lon' => -71.12]);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testGeoDistance()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
        {
          "match_all": {}
        }
      ],
      "filter": [
          {
            "geo_distance": {
              "distance": "200km",
              "pin.location": {
                "lat": 40,
                "lon": -70
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
            $bool->must(function (Query $must) {
                $must->matchAll();
            });
            $bool->filter(function (Query $filter) {
                $filter->geoDistance(function (GeoDistance $geoDistance) {
                    $geoDistance->distance('200km');
                    $geoDistance->location('pin.location', ['lat' => 40, 'lon' => -70]);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testGeoGrid()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "geo_grid" :{
      "location" : {
        "geohash" : "u0"
      }
    }
  }
}
JSON;
        $query = new Query();
        $query->geoGrid('location', function (GeoGrid $geoGrid) {
            $geoGrid->geohash('u0');
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testGeoPolygon()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [
      {
        "match_all": {}
      }
      ],
      "filter": [
        {
        "geo_polygon": {
          "person.location": {
            "points": [
              { "lat": 40, "lon": -70 },
              { "lat": 30, "lon": -80 },
              { "lat": 20, "lon": -90 }
            ]
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
            $bool->must(function (Query $must) {
                $must->matchAll();
            });
            $bool->filter(function (Query $filter) {
                $filter->geoPolygon('person.location', function (GeoPolygon $geoPolygon) {
                    $geoPolygon->points([
                        ['lat' => 40, 'lon' => -70],
                        ['lat' => 30, 'lon' => -80],
                        ['lat' => 20, 'lon' => -90],
                    ]);
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }

    public function testGeoShape()
    {
        $exampleJson = <<<JSON
{
  "query": {
    "bool": {
      "must": [{
        "match_all": {}
      }],
      "filter": [{
        "geo_shape": {
          "location": {
            "shape": {
              "type": "envelope",
              "coordinates": [ [ 13.0, 53.0 ], [ 14.0, 52.0 ] ]
            },
            "relation": "within"
          }
        }
      }]
    }
  }
}
JSON;
        $query = new Query();
        $query->bool(function (Boolean $bool) {
            $bool->must(function (Query $must) {
                $must->matchAll();
            });
            $bool->filter(function (Query $filter) {
                $filter->geoShape('location', function (GeoShape $geoShape) {
                    $geoShape->shape(['type' => 'envelope', 'coordinates' => [[13.0, 53.0], [14.0, 52.0]]]);
                    $geoShape->relation('within');
                });
            });
        });
        $this->assertQuery($exampleJson, $query);
    }
}

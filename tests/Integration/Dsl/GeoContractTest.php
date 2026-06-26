<?php

declare(strict_types=1);

namespace Tests\Integration\Dsl;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\Geo\GeoBoundingBox;
use ElasticKit\DSL\Queries\Geo\GeoDistance;
use Tests\Integration\IntegrationTestCase;

class GeoContractTest extends IntegrationTestCase
{
    public function testGeoDistance(): void
    {
        // 200km from NYC -> doc 1 (0km), doc 2 (~115km); doc 3 (~350km) excluded
        $q = (new Query())->geoDistance(function (GeoDistance $g) {
            $g->distance('200km')->location('location', ['lat' => 40.7, 'lon' => -74.0]);
        });
        $this->assertQueryEs($q, 2);
    }

    public function testGeoBoundingBox(): void
    {
        // box covering lat 38-42, lon -75..-70 -> all 3 docs
        $q = (new Query())->geoBoundingBox('location', function (GeoBoundingBox $g) {
            $g->topLeft(['lat' => 42, 'lon' => -75])->bottomRight(['lat' => 38, 'lon' => -70]);
        });
        $this->assertQueryEs($q, 3);
    }
}

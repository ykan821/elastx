<?php

declare(strict_types=1);

namespace Tests\Integration;

use ElasticKit\DSL\Query;

/**
 * Verifies the integration harness itself: ES reachable, random index
 * created, seed data present.
 */
class SmokeTest extends IntegrationTestCase
{
    public function testEsReachableAndIndexSeeded(): void
    {
        // matchAll hits the 3 seeded docs.
        $this->assertQueryEs((new Query())->matchAll(), 3);
    }
}

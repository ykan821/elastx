<?php

declare(strict_types=1);

namespace Tests\Integration\Index;

use Tests\Integration\IntegrationTestCase;

class StatsSupportContractTest extends IntegrationTestCase
{
    public function testMax(): void
    {
        $this->assertEquals(35.0, $this->makeIndex()->newQuery()->matchAll()->max('price'));
    }

    public function testMin(): void
    {
        $this->assertEquals(25.0, $this->makeIndex()->newQuery()->matchAll()->min('price'));
    }

    public function testSum(): void
    {
        $this->assertEquals(90.0, $this->makeIndex()->newQuery()->matchAll()->sum('price'));
    }

    public function testAvg(): void
    {
        $this->assertEqualsWithDelta(30.0, $this->makeIndex()->newQuery()->matchAll()->avg('price'), 0.01);
    }

    public function testStats(): void
    {
        $stats = $this->makeIndex()->newQuery()->matchAll()->stats('price');
        $this->assertSame(3, $stats['count']);
        $this->assertEquals(25.0, $stats['min']);
        $this->assertEquals(35.0, $stats['max']);
        $this->assertEquals(90.0, $stats['sum']);
    }
}

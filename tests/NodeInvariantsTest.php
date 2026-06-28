<?php

use Tests\DslTestCase;
use ElasticKit\DSL\Queries\TermLevel\Term;

/**
 * Invariants for Node::toArray() — pins down the serialization branches driven
 * by the $_value / $_properties (null vs []) / $_fieldKeyed / $_valueKey
 * combination. These boundaries are where P0 data-loss bugs historically lived
 * (value drop, float drop, empty-bool).
 */
class NodeInvariantsTest extends DslTestCase
{
    // value set, no properties -> shorthand (no valueKey wrap)
    public function testValueOnlyProducesShorthand()
    {
        $t = new Term('status', 'published');
        $this->assertSame(['status' => 'published'], $t->toArray());
    }

    // value set + extra property -> value promoted under $_valueKey
    public function testValueWithPropertyPromotesToValueKey()
    {
        $t = new Term('status', 'published');
        $t->boost(2.0);
        $this->assertEquals(['status' => ['value' => 'published', 'boost' => 2.0]], $t->toArray());
    }

    // value set + property occupying $_valueKey -> property wins, value NOT promoted
    public function testValueDoesNotOverwritePropertyAtValueKey()
    {
        $t = new Term('status', 'published');
        $t->value('override');
        $this->assertSame(['status' => ['value' => 'override']], $t->toArray());
    }

    // no value, no properties, fieldKeyed -> null (not stdClass, not omitted)
    public function testEmptyFieldKeyedProducesNull()
    {
        $t = new Term('status', function (Term $t) {
            // empty closure: no clauses set
        });
        $this->assertSame(['status' => null], $t->toArray());
    }

    // no value, properties set -> properties only (value key absent)
    public function testPropertiesOnlyWithoutValue()
    {
        $t = new Term('status', ['value' => 'x']);
        $this->assertSame(['status' => ['value' => 'x']], $t->toArray());
    }

    // float value survives serialization (the JSON_PRESERVE_ZERO_FRACTION boundary)
    public function testFloatValueSurvivesToJson()
    {
        $t = new Term('status', 'published');
        $t->boost(2.0);
        // boost 2.0 must stay a float in JSON, not collapse to int 2
        $this->assertStringContainsString('"boost": 2.0', $t->toJson());
    }

    // field-keyed node with no field set -> LogicException (not uncatchable Error)
    public function testFieldKeyedWithoutFieldThrows()
    {
        $this->expectException(\LogicException::class);
        (new Term())->toArray();
    }

    // field-keyed node that overrides toArray() (Intervals) must be guarded too
    public function testFieldKeyedOverrideWithoutFieldThrows()
    {
        $this->expectException(\LogicException::class);
        (new \ElasticKit\DSL\Queries\FullText\Intervals())->toArray();
    }
}

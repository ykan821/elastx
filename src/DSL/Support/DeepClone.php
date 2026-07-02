<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Support;

use Closure;
use ReflectionClass;

/**
 * Deep clones an object via reflection: every non-static object/array property
 * (own and inherited) is duplicated so the clone shares no references with its
 * original. New properties are covered automatically — no per-class __clone.
 *
 *
 * @internal
 */
trait DeepClone
{
    /** @var array<class-string, list<\ReflectionProperty>> */
    private static array $cloneProperties = [];

    public function __clone(): void
    {
        $class = static::class;
        if (!isset(self::$cloneProperties[$class])) {
            self::$cloneProperties[$class] = array_filter(
                (new ReflectionClass($class))->getProperties(),
                fn ($p) => !$p->isStatic()
            );
        }

        foreach (self::$cloneProperties[$class] as $property) {
            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            if (is_array($value)) {
                $property->setValue($this, self::cloneArray($value));
            } elseif (is_object($value) && !($value instanceof Closure)) {
                $property->setValue($this, clone $value);
            }
        }
    }

    /**
     * Recursively clone object entries in an array.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    private static function cloneArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::cloneArray($value);
            } elseif (is_object($value) && !($value instanceof Closure)) {
                $array[$key] = clone $value;
            }
        }

        return $array;
    }
}

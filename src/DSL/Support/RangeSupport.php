<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Support;

use InvalidArgumentException;

/**
 * Maps operator shorthands (>=, >, <=, <) and [start, end] to ES range keys.
 */
trait RangeSupport
{
    /**
     * Normalize range operator shorthands before passing to parent constructor.
     *
     * @param mixed $field
     * @param mixed $value
     */
    public function __construct($field = null, $value = null)
    {
        if (is_array($value)) {
            $value = self::normalizeKeys($value);
        } elseif (is_array($field)) {
            $field = self::normalizeKeys($field);
        }
        parent::__construct($field, $value);
    }

    /**
     * @param array<int|string, mixed> $props
     * @return array<string, mixed>
     */
    private static function normalizeKeys(array $props): array
    {
        $operators = [
            '>=' => 'gte', '>' => 'gt', '<=' => 'lte', '<' => 'lt',
            0 => 'gte', 1 => 'lte', // range('field', [start, end])
        ];
        foreach ($props as $operator => $val) {
            if (isset($operators[$operator])) {
                unset($props[$operator]);
                $props[$operators[$operator]] = $val;
            } elseif (is_int($operator)) {
                // A positional element beyond the [start, end] shorthand is invalid;
                // without this guard it leaks into DSL as a numeric string key.
                throw new InvalidArgumentException(sprintf(
                    'Range shorthand only supports two positional elements [start, end]; '
                    . 'unexpected element at index %d. Use [\'gte\' => ..., \'lte\' => ...] instead.',
                    $operator
                ));
            }
        }
        return $props;
    }
}

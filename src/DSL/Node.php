<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

use Closure;
use InvalidArgumentException;
use stdClass;

/**
 * Abstract base class for DSL nodes (query types, params).
 *
 * @phpstan-consistent-constructor
 */
abstract class Node
{
    use DeepClone;

    /**
     * Properties owned by a node. Either an array of attributes, or null when
     * the node carries no properties (empty construction / empty closure,
     * which serializes to null). Field-value shorthand uses $_value.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $_properties = null;

    /**
     * Scalar value stored separately from properties.
     * When set, toArray() outputs shorthand (field => value) if no extra
     * properties exist, or promotes it under $_valueKey when properties are present.
     *
     * @var scalar|null
     */
    protected int|float|string|bool|null $_value = null;

    /**
     * The key used when promoting $_value into the properties array.
     * Override in subclasses that use a different key (e.g. 'query' for match queries).
     *
     * @var string
     */
    protected string $_valueKey = 'value';

    /**
     * Whether to use a field name as the top-level attribute of a node.
     *
     * @var bool
     */
    protected bool $_fieldKeyed = false;

    /**
     * The field name used as the top-level attribute of a node.
     *
     * @var string
     */
    protected string $_field;

    /**
     * Whether the node supports multiple clauses.
     *
     * @var bool
     */
    protected bool $_multi = false;

    /**
     * The Elasticsearch query or aggregation type identifier.
     *
     * @var string
     */
    protected string $_key;

    /**
     * Initialize the node.
     *
     * Accepts all input forms:
     * - new Term('status', 'published')  — K,V scalar
     * - new Term('status', [...])         — K,V array
     * - new Term('status', fn($t) => ...) — K,V closure
     * - new Term([...])                   — array properties
     * - new Term(fn($t) => ...)           — closure
     * - new Script('_score * ...')        — bare scalar (whole node value)
     * - new Term()                        — empty
     *
     * @param mixed $field Properties, field name, closure, scalar value, or null
     * @param mixed $value Value/properties/closure when using two-arg mode
     */
    public function __construct($field = null, $value = null)
    {
        if ($value !== null) {
            $this->fromKeyValue($field, $value);
        } elseif ($field instanceof Closure) {
            $this->fromClosure($field);
        } elseif ($this->_fieldKeyed && is_array($field)) {
            $this->fromArrayField($field);
        } elseif (is_scalar($field)) {
            $this->fromScalar($field);
        } elseif (is_array($field)) {
            $this->fromArrayProperties($field);
        }
    }

    /**
     * Initialize from a field-value pair.
     *
     * @param mixed $field
     * @param mixed $value
     */
    protected function fromKeyValue($field, $value): void
    {
        if ($value instanceof Closure) {
            $value($this);
        } elseif (is_scalar($value)) {
            $this->_value = $value;
            $this->_properties = [];
        } elseif (is_array($value)) {
            $this->_properties = $value;
        } else {
            throw new InvalidArgumentException(sprintf(
                '%s does not accept %s as a field value; use a clause key, closure, scalar, or array.',
                static::class,
                get_debug_type($value)
            ));
        }
        if ($this->_fieldKeyed) {
            $this->field($field);
        }
    }

    /**
     * Initialize from a closure.
     *
     * @param Closure $closure
     */
    protected function fromClosure(Closure $closure): void
    {
        $closure($this);
    }

    /**
     * Initialize from a single-element array where key is field name.
     *
     * @param array<string, mixed> $field
     */
    protected function fromArrayField(array $field): void
    {
        foreach ($field as $key => $val) {
            $this->field($key);
            if (is_scalar($val)) {
                $this->_value = $val;
                $this->_properties = [];
            } elseif (is_array($val)) {
                $this->_properties = $val;
            }
            break;
        }
    }

    /**
     * Initialize from an array of properties.
     *
     * Default: store as-is. Override to route specific keys through
     * clause accumulators (addClause) instead of raw addProperty.
     *
     * @param array<string, mixed> $field
     */
    protected function fromArrayProperties(array $field): void
    {
        $this->_properties = $field;
    }

    /**
     * Initialize from a scalar value.
     *
     * @param mixed $value
     */
    protected function fromScalar($value): void
    {
        $this->_value = $value;
        $this->_properties = [];
    }

    /**
     * Set whether this node uses a field name as the top-level attribute.
     *
     * @param bool $fieldKeyed
     * @return static
     */
    protected function fieldKeyed(bool $fieldKeyed): static
    {
        $this->_fieldKeyed = $fieldKeyed;
        return $this;
    }

    /**
     * Whether the node supports multiple clauses.
     *
     * @param bool $multi
     * @return static
     */
    protected function multi(bool $multi): static
    {
        $this->_multi = $multi;
        return $this;
    }

    /**
     * Get the Elasticsearch type identifier.
     *
     * @internal
     *
     * @return string
     */
    public function key(): string
    {
        return $this->_key;
    }

    /**
     * Set the field name as the top-level attribute of a node.
     *
     * @param string $field
     * @return static
     */
    public function field($field): static
    {
        if ($this->_fieldKeyed) {
            $this->_field = $field;
        } else {
            $this->_properties['field'] = $field;
        }
        return $this;
    }

    /**
     * Add a property to a node.
     *
     * @param string $attribute
     * @param mixed $value
     * @param bool $append
     * @return static
     */
    protected function addProperty($attribute, $value, $append = false): static
    {
        if ($append) {
            $this->_properties[$attribute][] = $value;
        } else {
            $this->_properties[$attribute] = $value;
        }
        return $this;
    }

    /**
     * Static factory — thin proxy over the constructor.
     *
     * Only intercepts same-class instance reuse; everything else
     * delegates to __construct($field, $value).
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public static function create($field = null, $value = null): static
    {
        if ($value === null && $field instanceof static) {
            return $field;
        }
        return new static($field, $value);
    }

    /**
     * Resolve nested Query and Node instances in a properties array.
     *
     * @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    protected function resolveProperties(array $properties): array
    {
        foreach ($properties as $key => $property) {
            if ($property instanceof Query) {
                $properties[$key] = $property->toArray()['query'] ?? null;
            } elseif ($property instanceof Node) {
                $properties[$key] = $property->toArray();
            } elseif ($property instanceof Closure) {
                $properties[$key] = Query::create($property)->toArray()['query'] ?? null;
            } elseif (is_array($property)) {
                $properties[$key] = $this->resolveProperties($property);
            }
        }
        return array_filter($properties, fn ($v) => $v !== null);
    }

    /**
     * Serialize to an Elasticsearch DSL array.
     *
     * Recursively resolves nested Query and Node instances.
     * When _fieldKeyed is true, wraps properties under the field name.
     *
     * @return array|mixed
     */
    public function toArray()
    {
        if ($this->_value !== null) {
            $props = $this->_properties ?? [];
            if ($props === []) {
                $properties = $this->_value;
            } else {
                $properties = $this->resolveProperties($props);
                if (!isset($properties[$this->_valueKey])) {
                    $properties = array_merge([$this->_valueKey => $this->_value], $properties);
                }
            }
        } else {
            if (empty($this->_properties)) {
                $properties = $this->_fieldKeyed ? null : new stdClass();
            } else {
                $properties = $this->resolveProperties($this->_properties);
            }
        }

        if ($this->_fieldKeyed) {
            return [$this->_field => $properties];
        }
        return $properties;
    }

    /**
     * Convert the node to a JSON string.
     *
     * @param int $flags
     * @param int $depth
     * @return string
     */
    public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, int $depth = 512): string
    {
        $json = json_encode($this->toArray(), $flags, $depth);

        return $json === false ? '' : $json;
    }

    /**
     * Convert the node to a JSON string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Floating point number used to decrease or increase
     * the relevance scores of a query. Defaults to 1.0.
     *
     * @param float $value
     * @return static
     */
    public function boost($value): static
    {
        return $this->addProperty('boost', $value);
    }
}

<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

use Closure;

/**
 * Abstract base class for DSL nodes (query types, params).
 *
 * @phpstan-consistent-constructor
 */
abstract class Node
{
    /**
     * Properties owned by a node. Either an array of attributes, or null when
     * the node carries no properties (empty construction / empty closure,
     * which serializes to null). Whole-value pass-through uses $_raw;
     * field-value shorthand uses $_rawValue.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $_properties = null;

    /**
     * Raw whole-value pass-through. When set, toArray() emits this value
     * verbatim — for nodes constructed with a single non-array, non-closure
     * argument (a wrapped Node, a FilterAgg filter query, etc.).
     *
     * @var mixed
     */
    protected $_raw;

    /**
     * Raw scalar value stored separately from properties.
     * When set, toArray() outputs shorthand (field => value) if no extra
     * properties exist, or promotes it under $_valueKey when properties are present.
     *
     * @var scalar|null
     */
    protected $_rawValue;

    /**
     * The key used when promoting $_rawValue into the properties array.
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
    protected $_field;

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
     * - new Term()                        — empty
     *
     * @param mixed $field Properties, field name, closure, or null
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
        } elseif ($this->_fieldKeyed && is_scalar($field)) {
            $this->fromScalar($field);
        } elseif (is_array($field)) {
            $this->_properties = $field;
        } elseif ($field !== null) {
            $this->_raw = $field;
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
            $this->_rawValue = $value;
            $this->_properties = [];
        } elseif (is_array($value)) {
            $this->_properties = $value;
        } else {
            $this->_raw = $value;
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
                $this->_rawValue = $val;
                $this->_properties = [];
            } elseif (is_array($val)) {
                $this->_properties = $val;
            } else {
                $this->_raw = $val;
            }
            break;
        }
    }

    /**
     * Initialize from a scalar value.
     *
     * @param mixed $value
     */
    protected function fromScalar($value): void
    {
        $this->_rawValue = $value;
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
     * Whether the node supports multiple clauses.
     *
     * @return bool
     */
    protected function isMulti(): bool
    {
        return $this->_multi;
    }

    /**
     * Get the Elasticsearch type identifier.
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
    public function addProperty($attribute, $value, $append = false): static
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
                $properties[$key] = $property->toArray()['query'];
            } elseif ($property instanceof Node) {
                $properties[$key] = $property->toArray();
            } elseif ($property instanceof Closure) {
                $properties[$key] = Query::create($property)->toArray()['query'];
            } elseif (is_array($property)) {
                $properties[$key] = $this->resolveProperties($property);
            }
        }
        return $properties;
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
        if ($this->_raw !== null) {
            $properties = $this->_raw;
        } elseif ($this->_rawValue !== null) {
            $props = $this->_properties ?? [];
            if ($props === []) {
                $properties = $this->_rawValue;
            } else {
                $properties = $this->resolveProperties($props);
                if (!isset($properties[$this->_valueKey])) {
                    $properties = array_merge([$this->_valueKey => $this->_rawValue], $properties);
                }
            }
        } else {
            $properties = $this->_properties === null ? null : $this->resolveProperties($this->_properties);
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
     * @param float $boost
     * @return static
     */
    public function boost($boost): static
    {
        return $this->addProperty('boost', $boost);
    }
}

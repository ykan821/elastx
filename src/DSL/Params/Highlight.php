<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Params;

use stdClass;
use ElasticKit\DSL\Query;
use ElasticKit\DSL\Node;

/**
 * Highlights search matches in field values.
 *
 * @phpstan-consistent-constructor
 */
class Highlight extends Node
{
    protected string $_key = 'highlight';

    /**
     * Create an instance from various input formats.
     *
     * - String: creates instance with the field highlighted (shorthand for field()).
     * - Other: delegates to parent::create().
     *
     * @param mixed $field
     * @param mixed $value
     * @return static
     */
    public static function create($field = null, $value = null): static
    {
        if ($value === null && is_string($field)) {
            return (new static())->field($field);
        }
        return parent::create($field, $value);
    }

    /**
     * Add a field to highlight. Empty settings produces `{}`.
     *
     * @param string $field
     * @param array<string, mixed> $settings
     * @return static
     */
    public function field($field, array $settings = []): static
    {
        $value = empty($settings) ? new stdClass() : $settings;
        $this->_properties['fields'][$field] = $value;
        return $this;
    }

    /**
     * Opening HTML tags for highlighted snippets.
     *
     * @param array<int, string> $tags
     * @return static
     */
    public function preTags(array $tags): static
    {
        return $this->addProperty('pre_tags', $tags);
    }

    /**
     * Closing HTML tags for highlighted snippets.
     *
     * @param array<int, string> $tags
     * @return static
     */
    public function postTags(array $tags): static
    {
        return $this->addProperty('post_tags', $tags);
    }

    /**
     * Size of a highlighted fragment. Defaults to 100.
     *
     * @param int $size
     * @return static
     */
    public function fragmentSize(int $size): static
    {
        return $this->addProperty('fragment_size', $size);
    }

    /**
     * Maximum number of fragments to return.
     *
     * @param int $num
     * @return static
     */
    public function numberOfFragments(int $num): static
    {
        return $this->addProperty('number_of_fragments', $num);
    }

    /**
     * Highlighter encoder: html or default.
     *
     * @param string $encoder
     * @return static
     */
    public function encoder(string $encoder): static
    {
        return $this->addProperty('encoder', $encoder);
    }

    /**
     * Sort order for highlighted fragments: score or none.
     *
     * @param string $order
     * @return static
     */
    public function order(string $order): static
    {
        return $this->addProperty('order', $order);
    }

    /**
     * Highlight against a query other than the search query.
     *
     * @param mixed $query
     * @return static
     */
    public function highlightQuery($query): static
    {
        return $this->addProperty('highlight_query', Query::create($query));
    }

    /**
     * Highlighter type: unified, plain, or fvh.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->addProperty('type', $type);
    }

    /**
     * Boundary scanner: chars, sentence, or word.
     *
     * @param string $scanner
     * @return static
     */
    public function boundaryScanner(string $scanner): static
    {
        return $this->addProperty('boundary_scanner', $scanner);
    }

    /**
     * Locale for the boundary scanner.
     *
     * @param string $locale
     * @return static
     */
    public function boundaryScannerLocale(string $locale): static
    {
        return $this->addProperty('boundary_scanner_locale', $locale);
    }

    /**
     * Maximum distance for the boundary scanner.
     *
     * @param int $max
     * @return static
     */
    public function boundaryMaxScan(int $max): static
    {
        return $this->addProperty('boundary_max_scan', $max);
    }

    /**
     * Size of snippet when no matching fragment is found.
     *
     * @param int $size
     * @return static
     */
    public function noMatchSize(int $size): static
    {
        return $this->addProperty('no_match_size', $size);
    }

    /**
     * Fragmenter: simple or span (plain highlighter only).
     *
     * @param string $fragmenter
     * @return static
     */
    public function fragmenter(string $fragmenter): static
    {
        return $this->addProperty('fragmenter', $fragmenter);
    }

    public function toArray()
    {
        $result = parent::toArray();
        if (isset($result['highlight_query']) && $result['highlight_query'] instanceof Query) {
            $result['highlight_query'] = $result['highlight_query']->toArray()['query'] ?? new stdClass();
        }
        return $result;
    }
}

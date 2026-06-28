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
     * @param array<int, string> $value
     * @return static
     */
    public function preTags(array $value): static
    {
        return $this->addProperty('pre_tags', $value);
    }

    /**
     * Closing HTML tags for highlighted snippets.
     *
     * @param array<int, string> $value
     * @return static
     */
    public function postTags(array $value): static
    {
        return $this->addProperty('post_tags', $value);
    }

    /**
     * Size of a highlighted fragment. Defaults to 100.
     *
     * @param int $value
     * @return static
     */
    public function fragmentSize(int $value): static
    {
        return $this->addProperty('fragment_size', $value);
    }

    /**
     * Maximum number of fragments to return.
     *
     * @param int $value
     * @return static
     */
    public function numberOfFragments(int $value): static
    {
        return $this->addProperty('number_of_fragments', $value);
    }

    /**
     * Highlighter encoder: html or default.
     *
     * @param string $value
     * @return static
     */
    public function encoder(string $value): static
    {
        return $this->addProperty('encoder', $value);
    }

    /**
     * Sort order for highlighted fragments: score or none.
     *
     * @param string $value
     * @return static
     */
    public function order(string $value): static
    {
        return $this->addProperty('order', $value);
    }

    /**
     * Highlight against a query other than the search query.
     *
     * @param mixed $value
     * @return static
     */
    public function highlightQuery($value): static
    {
        return $this->addProperty('highlight_query', Query::create($value));
    }

    /**
     * Highlighter type: unified, plain, or fvh.
     *
     * @param string $value
     * @return static
     */
    public function type(string $value): static
    {
        return $this->addProperty('type', $value);
    }

    /**
     * Boundary scanner: chars, sentence, or word.
     *
     * @param string $value
     * @return static
     */
    public function boundaryScanner(string $value): static
    {
        return $this->addProperty('boundary_scanner', $value);
    }

    /**
     * Locale for the boundary scanner.
     *
     * @param string $value
     * @return static
     */
    public function boundaryScannerLocale(string $value): static
    {
        return $this->addProperty('boundary_scanner_locale', $value);
    }

    /**
     * Maximum distance for the boundary scanner.
     *
     * @param int $value
     * @return static
     */
    public function boundaryMaxScan(int $value): static
    {
        return $this->addProperty('boundary_max_scan', $value);
    }

    /**
     * Size of snippet when no matching fragment is found.
     *
     * @param int $value
     * @return static
     */
    public function noMatchSize(int $value): static
    {
        return $this->addProperty('no_match_size', $value);
    }

    /**
     * Fragmenter: simple or span (plain highlighter only).
     *
     * @param string $value
     * @return static
     */
    public function fragmenter(string $value): static
    {
        return $this->addProperty('fragmenter', $value);
    }
}

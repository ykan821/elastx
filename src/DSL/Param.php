<?php

declare(strict_types=1);

namespace ElasticKit\DSL;

/**
 * Search request level parameters such as size, from, sort, etc.
 */
trait Param
{
    /**
     * Search request parameters stored independently from query clauses.
     *
     * @var array<string, mixed>
     */
    protected array $_params = [];

    /**
     * Check if a search request parameter has been set.
     *
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key): bool
    {
        return array_key_exists($key, $this->_params);
    }

    /**
     * Defines the maximum number of documents to return.
     * Defaults to 10.
     *
     * @param int $value
     * @return $this
     */
    public function size($value): static
    {
        $this->_params['size'] = $value;
        return $this;
    }

    /**
     * The starting document offset.
     * Defaults to 0.
     *
     * @param int $value
     * @return $this
     */
    public function from($value): static
    {
        $this->_params['from'] = $value;
        return $this;
    }

    /**
     * Specifies the period of time to wait for
     * a response from each shard.
     *
     * @param string $value
     * @return $this
     */
    public function timeout($value): static
    {
        $this->_params['timeout'] = $value;
        return $this;
    }

    /**
     * Minimum relevance score required for a document
     * to be included in the result set.
     *
     * @param float $value
     * @return $this
     */
    public function minScore($value): static
    {
        $this->_params['min_score'] = $value;
        return $this;
    }

    /**
     * Maximum number of documents to collect for
     * each shard, upon reaching which the query execution will terminate early.
     *
     * @param int $value
     * @return $this
     */
    public function terminateAfter($value): static
    {
        $this->_params['terminate_after'] = $value;
        return $this;
    }

    /**
     * If true, returns detailed information about
     * score computation as part of a hit.
     *
     * @param bool $value
     * @return $this
     */
    public function explain($value): static
    {
        $this->_params['explain'] = $value;
        return $this;
    }

    /**
     * If true, returns document version as part
     * of a hit.
     *
     * @param bool $value
     * @return $this
     */
    public function version($value): static
    {
        $this->_params['version'] = $value;
        return $this;
    }

    /**
     * If true, the query is profiled.
     *
     * @param bool $value
     * @return $this
     */
    public function profile($value): static
    {
        $this->_params['profile'] = $value;
        return $this;
    }

    /**
     * Number of hits matching the query to count
     * accurately. Defaults to 10,000.
     *
     * @param bool|int $value
     * @return $this
     */
    public function trackTotalHits($value): static
    {
        $this->_params['track_total_hits'] = $value;
        return $this;
    }

    /**
     * If true, returns sequence number and primary
     * term of the last modification of each hit.
     *
     * @param bool $value
     * @return $this
     */
    public function seqNoPrimaryTerm($value): static
    {
        $this->_params['seq_no_primary_term'] = $value;
        return $this;
    }

    /**
     * Sorts the response by the given criteria. Appends to the sort list;
     * multiple calls chain together.
     *
     * - sort('price', 'asc') — field + order
     * - sort('price', ['order' => 'asc', 'mode' => 'avg']) — field + options
     * - sort('_score') — field without direction
     * - sort([['price' => 'asc'], ['age' => 'desc']]) — raw ES list, each spec appended
     *
     * @param string|array<int, mixed> $field
     * @param string|array<string, mixed>|null $order
     * @return $this
     */
    public function sort($field, $order = null): static
    {
        if ($order !== null) {
            $this->_params['sort'][] = [$field => $order];
        } elseif (is_array($field)) {
            foreach ($field as $spec) {
                $this->_params['sort'][] = $spec;
            }
        } else {
            $this->_params['sort'][] = $field;
        }
        return $this;
    }

    /**
     * Indicates which source fields are returned
     * for the search hits.
     *
     * @param array<int, string>|string $value
     * @return $this
     */
    public function source($value): static
    {
        $this->_params['_source'] = $value;
        return $this;
    }

    /**
     * Sort values used to paginate results.
     *
     * @param array<int, mixed> $value
     * @return $this
     */
    public function searchAfter($value): static
    {
        $this->_params['search_after'] = $value;
        return $this;
    }

    /**
     * Controls which stored fields are returned
     * as part of a hit.
     *
     * @param array<int, string> $value
     * @return $this
     */
    public function storedFields($value): static
    {
        $this->_params['stored_fields'] = $value;
        return $this;
    }

    /**
     * Returns docvalue fields as part of a hit.
     *
     * @param array<int, mixed> $value
     * @return $this
     */
    public function docvalueFields($value): static
    {
        $this->_params['docvalue_fields'] = $value;
        return $this;
    }

    /**
     * Boosts the _score of documents from specified indices.
     * Appends to the indices_boost list; multiple calls chain together.
     *
     * @param array<string, float> $value
     * @return $this
     */
    public function indicesBoost($value): static
    {
        $this->_params['indices_boost'][] = $value;
        return $this;
    }

    /**
     * If true, compute and return _score even when
     * sorting on a field. Defaults to false.
     *
     * @param bool $value
     * @return $this
     */
    public function trackScores($value): static
    {
        $this->_params['track_scores'] = $value;
        return $this;
    }

    /**
     * Returns values from fields in the search response.
     * Supports field alias fields and array fields.
     *
     * @param array<int, mixed> $value
     * @return $this
     */
    public function fields($value): static
    {
        $this->_params['fields'] = $value;
        return $this;
    }

    /**
     * Limits the search to a point in time (PIT).
     *
     * @param array<string, mixed> $value
     * @return $this
     */
    public function pit($value): static
    {
        $this->_params['pit'] = $value;
        return $this;
    }

    /**
     * Filter applied after query and aggregation execution.
     * Accepts a closure, array, or Query object.
     *
     * @param mixed $value
     * @return $this
     */
    public function postFilter($value): static
    {
        $this->_params['post_filter'] = Query::create($value);
        return $this;
    }

    /**
     * Collapse search results by field value.
     *
     * @param mixed $value
     * @return $this
     */
    public function collapse($value): static
    {
        $this->_params['collapse'] = Params\Collapse::create($value);
        return $this;
    }

    /**
     * Rescore the top documents with a secondary query.
     *
     * @param mixed $value
     * @return $this
     */
    public function rescore($value): static
    {
        $this->_params['rescore'] = Params\Rescore::create($value);
        return $this;
    }

    /**
     * Highlight search matches in field values.
     * Supports chaining — fields are merged across calls.
     *
     * @param mixed $value
     * @return $this
     */
    public function highlight($value): static
    {
        $new = Params\Highlight::create($value);

        if (isset($this->_params['highlight']) && $this->_params['highlight'] instanceof Params\Highlight) {
            // Merge new fields into existing highlight
            $existing = $this->_params['highlight'];
            if (is_array($new->_properties) && isset($new->_properties['fields'])) {
                foreach ($new->_properties['fields'] as $field => $settings) {
                    $existing->field($field, (array) $settings);
                }
            }
            // Merge other properties (pre_tags, post_tags, etc) — last wins
            if (is_array($new->_properties)) {
                foreach ($new->_properties as $key => $val) {
                    if ($key !== 'fields') {
                        $existing->addProperty($key, $val);
                    }
                }
            }
        } else {
            $this->_params['highlight'] = $new;
        }

        return $this;
    }

    /**
     * Search suggestions based on term, completion, or phrase.
     *
     * @param mixed $value
     * @return $this
     */
    public function suggest($value): static
    {
        $this->_params['suggest'] = Params\Suggest::create($value);
        return $this;
    }

    /**
     * Returns script evaluation values for each hit.
     *
     * @param array<string, mixed> $value
     * @return $this
     */
    public function scriptFields($value): static
    {
        $this->_params['script_fields'] = $value;
        return $this;
    }

    /**
     * Runtime field definitions used in the search request.
     *
     * @param array<string, mixed> $value
     * @return $this
     */
    public function runtimeMappings($value): static
    {
        $this->_params['runtime_mappings'] = $value;
        return $this;
    }

    /**
     * Performs a k-nearest neighbor (kNN) search on a dense_vector field.
     * Supports chaining — multiple calls append knn clauses as an array.
     *
     * - knn(array) — raw ES structure
     * - knn(closure) — receives a Knn node for fluent building
     * - knn(field, vector) — shorthand for field + query_vector
     *
     * @param mixed $knn
     * @param array<int|float>|null $queryVector
     * @return $this
     */
    public function knn($knn, $queryVector = null): static
    {
        if (is_string($knn) && $queryVector !== null) {
            $node = (new Params\Knn())->field($knn)->queryVector($queryVector);
        } else {
            $node = Params\Knn::create($knn);
        }

        if (isset($this->_params['knn'])) {
            if ($this->_params['knn'] instanceof Node) {
                $this->_params['knn'] = [$this->_params['knn']];
            }
            $this->_params['knn'][] = $node;
        } else {
            $this->_params['knn'] = $node;
        }

        return $this;
    }
}

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
    protected $_params = [];

    /**
     * Check if a search request parameter has been set.
     *
     * @param string $key
     * @return bool
     */
    public function hasParam($key)
    {
        return array_key_exists($key, $this->_params);
    }

    /**
     * Defines the maximum number of documents to return.
     * Defaults to 10.
     *
     * @param int $size
     * @return $this
     */
    public function size($size)
    {
        $this->_params['size'] = $size;
        return $this;
    }

    /**
     * The starting document offset.
     * Defaults to 0.
     *
     * @param int $from
     * @return $this
     */
    public function from($from)
    {
        $this->_params['from'] = $from;
        return $this;
    }

    /**
     * Specifies the period of time to wait for
     * a response from each shard.
     *
     * @param string $timeout
     * @return $this
     */
    public function timeout($timeout)
    {
        $this->_params['timeout'] = $timeout;
        return $this;
    }

    /**
     * Minimum relevance score required for a document
     * to be included in the result set.
     *
     * @param float $minScore
     * @return $this
     */
    public function minScore($minScore)
    {
        $this->_params['min_score'] = $minScore;
        return $this;
    }

    /**
     * Maximum number of documents to collect for
     * each shard, upon reaching which the query execution will terminate early.
     *
     * @param int $terminateAfter
     * @return $this
     */
    public function terminateAfter($terminateAfter)
    {
        $this->_params['terminate_after'] = $terminateAfter;
        return $this;
    }

    /**
     * If true, returns detailed information about
     * score computation as part of a hit.
     *
     * @param bool $explain
     * @return $this
     */
    public function explain($explain)
    {
        $this->_params['explain'] = $explain;
        return $this;
    }

    /**
     * If true, returns document version as part
     * of a hit.
     *
     * @param bool $version
     * @return $this
     */
    public function version($version)
    {
        $this->_params['version'] = $version;
        return $this;
    }

    /**
     * If true, the query is profiled.
     *
     * @param bool $profile
     * @return $this
     */
    public function profile($profile)
    {
        $this->_params['profile'] = $profile;
        return $this;
    }

    /**
     * Number of hits matching the query to count
     * accurately. Defaults to 10,000.
     *
     * @param bool|int $trackTotalHits
     * @return $this
     */
    public function trackTotalHits($trackTotalHits)
    {
        $this->_params['track_total_hits'] = $trackTotalHits;
        return $this;
    }

    /**
     * If true, returns sequence number and primary
     * term of the last modification of each hit.
     *
     * @param bool $seqNoPrimaryTerm
     * @return $this
     */
    public function seqNoPrimaryTerm($seqNoPrimaryTerm)
    {
        $this->_params['seq_no_primary_term'] = $seqNoPrimaryTerm;
        return $this;
    }

    /**
     * Sorts the response by the given criteria.
     *
     * - sort('price', 'asc') — field + order, supports chaining
     * - sort([['price' => 'asc']]) — raw ES array format
     * - sort('_score') — field without direction
     *
     * @param string|array<int, mixed> $field
     * @param string|null $order
     * @return $this
     */
    public function sort($field, $order = null)
    {
        if ($order !== null) {
            $this->_params['sort'][] = [$field => $order];
        } elseif (is_array($field)) {
            $this->_params['sort'] = $field;
        } else {
            $this->_params['sort'][] = $field;
        }
        return $this;
    }

    /**
     * Indicates which source fields are returned
     * for the search hits.
     *
     * @param array<int, string>|string $source
     * @return $this
     */
    public function source($source)
    {
        $this->_params['_source'] = $source;
        return $this;
    }

    /**
     * Sort values used to paginate results.
     *
     * @param array<int, mixed> $searchAfter
     * @return $this
     */
    public function searchAfter($searchAfter)
    {
        $this->_params['search_after'] = $searchAfter;
        return $this;
    }

    /**
     * Controls which stored fields are returned
     * as part of a hit.
     *
     * @param array<int, string> $storedFields
     * @return $this
     */
    public function storedFields($storedFields)
    {
        $this->_params['stored_fields'] = $storedFields;
        return $this;
    }

    /**
     * Returns docvalue fields as part of a hit.
     *
     * @param array<int, mixed> $docvalueFields
     * @return $this
     */
    public function docvalueFields($docvalueFields)
    {
        $this->_params['docvalue_fields'] = $docvalueFields;
        return $this;
    }

    /**
     * Boosts the _score of documents from
     * specified indices.
     *
     * @param array<string, float> $indicesBoost
     * @return $this
     */
    public function indicesBoost($indicesBoost)
    {
        $this->_params['indices_boost'] = [$indicesBoost];
        return $this;
    }

    /**
     * If true, compute and return _score even when
     * sorting on a field. Defaults to false.
     *
     * @param bool $trackScores
     * @return $this
     */
    public function trackScores($trackScores)
    {
        $this->_params['track_scores'] = $trackScores;
        return $this;
    }

    /**
     * Returns values from fields in the search response.
     * Supports field alias fields and array fields.
     *
     * @param array<int, mixed> $fields
     * @return $this
     */
    public function fields($fields)
    {
        $this->_params['fields'] = $fields;
        return $this;
    }

    /**
     * Limits the search to a point in time (PIT).
     *
     * @param array<string, mixed> $pit
     * @return $this
     */
    public function pit($pit)
    {
        $this->_params['pit'] = $pit;
        return $this;
    }

    /**
     * Filter applied after query and aggregation execution.
     * Accepts a closure, array, or Query object.
     *
     * @param mixed $filter
     * @return $this
     */
    public function postFilter($filter)
    {
        $this->_params['post_filter'] = Query::create($filter);
        return $this;
    }

    /**
     * Collapse search results by field value.
     *
     * @param mixed $collapse
     * @return $this
     */
    public function collapse($collapse)
    {
        $this->_params['collapse'] = Params\Collapse::create($collapse);
        return $this;
    }

    /**
     * Rescore the top documents with a secondary query.
     *
     * @param mixed $rescore
     * @return $this
     */
    public function rescore($rescore)
    {
        $this->_params['rescore'] = Params\Rescore::create($rescore);
        return $this;
    }

    /**
     * Highlight search matches in field values.
     * Supports chaining — fields are merged across calls.
     *
     * @param mixed $highlight
     * @return $this
     */
    public function highlight($highlight)
    {
        $new = Params\Highlight::create($highlight);

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
                foreach ($new->_properties as $key => $value) {
                    if ($key !== 'fields') {
                        $existing->addProperty($key, $value);
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
     * @param mixed $suggest
     * @return $this
     */
    public function suggest($suggest)
    {
        $this->_params['suggest'] = Params\Suggest::create($suggest);
        return $this;
    }

    /**
     * Returns script evaluation values for each hit.
     *
     * @param array<string, mixed> $scriptFields
     * @return $this
     */
    public function scriptFields($scriptFields)
    {
        $this->_params['script_fields'] = $scriptFields;
        return $this;
    }

    /**
     * Runtime field definitions used in the search request.
     *
     * @param array<string, mixed> $runtimeMappings
     * @return $this
     */
    public function runtimeMappings($runtimeMappings)
    {
        $this->_params['runtime_mappings'] = $runtimeMappings;
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
    public function knn($knn, $queryVector = null)
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

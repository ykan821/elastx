<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs;

use ElasticKit\DSL\Aggs\Bucket\AdjacencyMatrix;
use ElasticKit\DSL\Aggs\Bucket\AutoDateHistogram;
use ElasticKit\DSL\Aggs\Bucket\CategorizeText;
use ElasticKit\DSL\Aggs\Bucket\Composite;
use ElasticKit\DSL\Aggs\Bucket\DateHistogram;
use ElasticKit\DSL\Aggs\Bucket\DateRange;
use ElasticKit\DSL\Aggs\Bucket\DiversifiedSampler;
use ElasticKit\DSL\Aggs\Bucket\FilterAgg;
use ElasticKit\DSL\Aggs\Bucket\Filters;
use ElasticKit\DSL\Aggs\Bucket\GlobalAgg;
use ElasticKit\DSL\Aggs\Bucket\FrequentItemSets;
use ElasticKit\DSL\Aggs\Bucket\GeoDistance;
use ElasticKit\DSL\Aggs\Bucket\GeoHashGrid;
use ElasticKit\DSL\Aggs\Bucket\GeohexGrid;
use ElasticKit\DSL\Aggs\Bucket\GeotileGrid;
use ElasticKit\DSL\Aggs\Bucket\Histogram;
use ElasticKit\DSL\Aggs\Bucket\IpPrefix;
use ElasticKit\DSL\Aggs\Bucket\IpRange;
use ElasticKit\DSL\Aggs\Bucket\Missing;
use ElasticKit\DSL\Aggs\Bucket\MultiTerms;
use ElasticKit\DSL\Aggs\Bucket\Nested;
use ElasticKit\DSL\Aggs\Bucket\ParentAgg;
use ElasticKit\DSL\Aggs\Bucket\RandomSampler;
use ElasticKit\DSL\Aggs\Bucket\Range;
use ElasticKit\DSL\Aggs\Bucket\RareTerms;
use ElasticKit\DSL\Aggs\Bucket\ReverseNested;
use ElasticKit\DSL\Aggs\Bucket\SignificantTerms;
use ElasticKit\DSL\Aggs\Bucket\SignificantText;
use ElasticKit\DSL\Aggs\Bucket\Terms;
use ElasticKit\DSL\Aggs\Bucket\TimeSeries;
use ElasticKit\DSL\Aggs\Bucket\VariableWidthHistogram;

trait Bucket
{
    /**
     * Groups documents by field values into buckets.
     *
     * @param mixed $params
     * @return static
     */
    public function terms($params)
    {
        return $this->node(Terms::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Defines a single bucket that limits documents matching a query.
     *
     * @param mixed $filter
     * @return static
     */
    public function filter($filter)
    {
        $instance = new FilterAgg();
        $instance->setFilter($filter);
        return $this->node($instance);
    }

    /**
     * Defines multiple buckets from multiple filters, one per filter expression.
     *
     * @param mixed $params
     * @return static
     */
    public function filters($params)
    {
        return $this->node(Filters::create($params));
    }

    /**
     * Groups documents into buckets based on combinations of filter expressions.
     *
     * @param mixed $params
     * @return static
     */
    public function adjacencyMatrix($params)
    {
        return $this->node(AdjacencyMatrix::create($params));
    }

    /**
     * Automatically determines bucket intervals for date fields based on document count.
     *
     * @param mixed $params
     * @return static
     */
    public function autoDateHistogram($params)
    {
        return $this->node(AutoDateHistogram::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Extracts categories from text fields by tokenizing and grouping values.
     *
     * @param mixed $params
     * @return static
     */
    public function categorizeText($params)
    {
        return $this->node(CategorizeText::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Creates composite buckets from multiple source values, supporting pagination.
     *
     * @param mixed $params
     * @return static
     */
    public function composite($params)
    {
        return $this->node(Composite::create($params));
    }

    /**
     * Groups documents into buckets by date interval (e.g. per day, per month).
     *
     * @param mixed $params
     * @return static
     */
    public function dateHistogram($params)
    {
        return $this->node(DateHistogram::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into buckets by user-defined date ranges.
     *
     * @param mixed $params
     * @return static
     */
    public function dateRange($params)
    {
        return $this->node(DateRange::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Limits any child aggregations to a diversified sample of top-scoring documents.
     *
     * @param mixed $params
     * @return static
     */
    public function diversifiedSampler($params)
    {
        return $this->node(DiversifiedSampler::create($params));
    }

    /**
     * Finds frequently co-occurring item sets in array fields.
     *
     * @param mixed $params
     * @return static
     */
    public function frequentItemSets($params)
    {
        return $this->node(FrequentItemSets::create($params));
    }

    /**
     * Groups documents into buckets by distance ranges from a geo point.
     *
     * @param mixed $params
     * @return static
     */
    public function geoDistance($params)
    {
        return $this->node(GeoDistance::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into grid cells using geohash prefixes.
     *
     * @param mixed $params
     * @return static
     */
    public function geoHashGrid($params)
    {
        return $this->node(GeoHashGrid::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into grid cells using H3 hexagon indexes.
     *
     * @param mixed $params
     * @return static
     */
    public function geohexGrid($params)
    {
        return $this->node(GeohexGrid::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into grid cells using geotile prefixes.
     *
     * @param mixed $params
     * @return static
     */
    public function geotileGrid($params)
    {
        return $this->node(GeotileGrid::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Defines a single global bucket containing all documents, ignoring query scope.
     *
     * @return static
     */
    public function globalAggregation()
    {
        return $this->node(new GlobalAgg());
    }

    /**
     * Groups documents into buckets by numeric interval.
     *
     * @param mixed $params
     * @return static
     */
    public function histogram($params)
    {
        return $this->node(Histogram::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into buckets by IP address prefix.
     *
     * @param mixed $params
     * @return static
     */
    public function ipPrefix($params)
    {
        return $this->node(IpPrefix::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into buckets by user-defined IP address ranges.
     *
     * @param mixed $params
     * @return static
     */
    public function ipRange($params)
    {
        return $this->node(IpRange::create($params));
    }

    /**
     * Creates a single bucket for documents missing a field value.
     *
     * @param mixed $params
     * @return static
     */
    public function missing($params)
    {
        return $this->node(Missing::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into buckets by multiple field term combinations.
     *
     * @param mixed $params
     * @return static
     */
    public function multiTerms($params)
    {
        return $this->node(MultiTerms::create($params));
    }

    /**
     * Aggregates on nested documents within a parent document.
     *
     * @param mixed $params
     * @return static
     */
    public function nested($params)
    {
        return $this->node(Nested::create($params));
    }

    /**
     * Aggregates on parent documents from a child document context in a join relation.
     *
     * @param mixed $params
     * @return static
     */
    public function parent($params)
    {
        return $this->node(ParentAgg::create($params));
    }

    /**
     * Limits any child aggregations to a random sample of documents.
     *
     * @param mixed $params
     * @return static
     */
    public function randomSampler($params)
    {
        return $this->node(RandomSampler::create($params));
    }

    /**
     * Groups documents into buckets by user-defined numeric ranges.
     *
     * @param mixed $params
     * @return static
     */
    public function range($params)
    {
        return $this->node(Range::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into buckets by rare field values with low document counts.
     *
     * @param mixed $params
     * @return static
     */
    public function rareTerms($params)
    {
        return $this->node(RareTerms::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Aggregates on parent documents from within a nested aggregation context.
     *
     * @param mixed $params
     * @return static
     */
    public function reverseNested($params = [])
    {
        return $this->node(ReverseNested::create($params));
    }

    /**
     * Finds field values that are unusually common in a subset compared to the whole index.
     *
     * @param mixed $params
     * @return static
     */
    public function significantTerms($params)
    {
        return $this->node(SignificantTerms::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Finds significant terms from text field content without needing a sub-field.
     *
     * @param mixed $params
     * @return static
     */
    public function significantText($params)
    {
        return $this->node(SignificantText::create(is_string($params) ? ['field' => $params] : $params));
    }

    /**
     * Groups documents into time series buckets for time-series data.
     *
     * @param mixed $params
     * @return static
     */
    public function timeSeries($params)
    {
        return $this->node(TimeSeries::create($params));
    }

    /**
     * Groups documents into dynamically sized histogram buckets based on data distribution.
     *
     * @param mixed $params
     * @return static
     */
    public function variableWidthHistogram($params)
    {
        return $this->node(VariableWidthHistogram::create(is_string($params) ? ['field' => $params] : $params));
    }
}

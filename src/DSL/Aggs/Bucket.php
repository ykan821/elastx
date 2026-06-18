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
use ElasticKit\DSL\Aggs\Bucket\Filter;
use ElasticKit\DSL\Aggs\Bucket\Filters;
use ElasticKit\DSL\Aggs\Bucket\Global_;
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
use ElasticKit\DSL\Aggs\Bucket\Parent_;
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
     * @param mixed $value
     * @return static
     */
    public function terms($value): static
    {
        return $this->node(Terms::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Defines a single bucket that limits documents matching a query.
     *
     * @param mixed $value
     * @return static
     */
    public function filter($value): static
    {
        $instance = new Filter();
        $instance->filter($value);
        return $this->node($instance);
    }

    /**
     * Defines multiple buckets from multiple filters, one per filter expression.
     *
     * @param mixed $value
     * @return static
     */
    public function filters($value): static
    {
        return $this->node(Filters::create($value));
    }

    /**
     * Groups documents into buckets based on combinations of filter expressions.
     *
     * @param mixed $value
     * @return static
     */
    public function adjacencyMatrix($value): static
    {
        return $this->node(AdjacencyMatrix::create($value));
    }

    /**
     * Automatically determines bucket intervals for date fields based on document count.
     *
     * @param mixed $value
     * @return static
     */
    public function autoDateHistogram($value): static
    {
        return $this->node(AutoDateHistogram::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Extracts categories from text fields by tokenizing and grouping values.
     *
     * @param mixed $value
     * @return static
     */
    public function categorizeText($value): static
    {
        return $this->node(CategorizeText::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Creates composite buckets from multiple source values, supporting pagination.
     *
     * @param mixed $value
     * @return static
     */
    public function composite($value): static
    {
        return $this->node(Composite::create($value));
    }

    /**
     * Groups documents into buckets by date interval (e.g. per day, per month).
     *
     * @param mixed $value
     * @return static
     */
    public function dateHistogram($value): static
    {
        return $this->node(DateHistogram::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into buckets by user-defined date ranges.
     *
     * @param mixed $value
     * @return static
     */
    public function dateRange($value): static
    {
        return $this->node(DateRange::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Limits any child aggregations to a diversified sample of top-scoring documents.
     *
     * @param mixed $value
     * @return static
     */
    public function diversifiedSampler($value): static
    {
        return $this->node(DiversifiedSampler::create($value));
    }

    /**
     * Finds frequently co-occurring item sets in array fields.
     *
     * @param mixed $value
     * @return static
     */
    public function frequentItemSets($value): static
    {
        return $this->node(FrequentItemSets::create($value));
    }

    /**
     * Groups documents into buckets by distance ranges from a geo point.
     *
     * @param mixed $value
     * @return static
     */
    public function geoDistance($value): static
    {
        return $this->node(GeoDistance::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into grid cells using geohash prefixes.
     *
     * @param mixed $value
     * @return static
     */
    public function geoHashGrid($value): static
    {
        return $this->node(GeoHashGrid::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into grid cells using H3 hexagon indexes.
     *
     * @param mixed $value
     * @return static
     */
    public function geohexGrid($value): static
    {
        return $this->node(GeohexGrid::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into grid cells using geotile prefixes.
     *
     * @param mixed $value
     * @return static
     */
    public function geotileGrid($value): static
    {
        return $this->node(GeotileGrid::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Defines a single global bucket containing all documents, ignoring query scope.
     *
     * @return static
     */
    public function global(): static
    {
        return $this->node(new Global_());
    }

    /**
     * Groups documents into buckets by numeric interval.
     *
     * @param mixed $value
     * @return static
     */
    public function histogram($value): static
    {
        return $this->node(Histogram::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into buckets by IP address prefix.
     *
     * @param mixed $value
     * @return static
     */
    public function ipPrefix($value): static
    {
        return $this->node(IpPrefix::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into buckets by user-defined IP address ranges.
     *
     * @param mixed $value
     * @return static
     */
    public function ipRange($value): static
    {
        return $this->node(IpRange::create($value));
    }

    /**
     * Creates a single bucket for documents missing a field value.
     *
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->node(Missing::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into buckets by multiple field term combinations.
     *
     * @param mixed $value
     * @return static
     */
    public function multiTerms($value): static
    {
        return $this->node(MultiTerms::create($value));
    }

    /**
     * Aggregates on nested documents within a parent document.
     *
     * @param mixed $value
     * @return static
     */
    public function nested($value): static
    {
        return $this->node(Nested::create($value));
    }

    /**
     * Aggregates on parent documents from a child document context in a join relation.
     *
     * @param mixed $value
     * @return static
     */
    public function parent($value): static
    {
        return $this->node(Parent_::create($value));
    }

    /**
     * Limits any child aggregations to a random sample of documents.
     *
     * @param mixed $value
     * @return static
     */
    public function randomSampler($value): static
    {
        return $this->node(RandomSampler::create($value));
    }

    /**
     * Groups documents into buckets by user-defined numeric ranges.
     *
     * @param mixed $value
     * @return static
     */
    public function range($value): static
    {
        return $this->node(Range::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into buckets by rare field values with low document counts.
     *
     * @param mixed $value
     * @return static
     */
    public function rareTerms($value): static
    {
        return $this->node(RareTerms::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Aggregates on parent documents from within a nested aggregation context.
     *
     * @param mixed $value
     * @return static
     */
    public function reverseNested($value = []): static
    {
        return $this->node(ReverseNested::create($value));
    }

    /**
     * Finds field values that are unusually common in a subset compared to the whole index.
     *
     * @param mixed $value
     * @return static
     */
    public function significantTerms($value): static
    {
        return $this->node(SignificantTerms::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Finds significant terms from text field content without needing a sub-field.
     *
     * @param mixed $value
     * @return static
     */
    public function significantText($value): static
    {
        return $this->node(SignificantText::create(is_string($value) ? ['field' => $value] : $value));
    }

    /**
     * Groups documents into time series buckets for time-series data.
     *
     * @param mixed $value
     * @return static
     */
    public function timeSeries($value): static
    {
        return $this->node(TimeSeries::create($value));
    }

    /**
     * Groups documents into dynamically sized histogram buckets based on data distribution.
     *
     * @param mixed $value
     * @return static
     */
    public function variableWidthHistogram($value): static
    {
        return $this->node(VariableWidthHistogram::create(is_string($value) ? ['field' => $value] : $value));
    }
}

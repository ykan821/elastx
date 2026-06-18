<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Queries;

use ElasticKit\DSL\Query;
use ElasticKit\DSL\Queries\TermLevel\Exists;
use ElasticKit\DSL\Queries\TermLevel\Fuzzy;
use ElasticKit\DSL\Queries\TermLevel\IDs;
use ElasticKit\DSL\Queries\TermLevel\Prefix;
use ElasticKit\DSL\Queries\TermLevel\Range;
use ElasticKit\DSL\Queries\TermLevel\Regexp;
use ElasticKit\DSL\Queries\TermLevel\Term;
use ElasticKit\DSL\Queries\TermLevel\Terms;
use ElasticKit\DSL\Queries\TermLevel\TermsSet;
use ElasticKit\DSL\Queries\TermLevel\Wildcard;

trait TermLevel
{
    /**
     * Returns documents that contain terms similar to the search term, as measured by a Levenshtein edit distance.
     *
     * An edit distance is the number of one-character changes needed to turn one term into another. These changes can include:
     *
     * Changing a character (box → fox);
     * Removing a character (black → lack);
     * Inserting a character (sic → sick);
     * Transposing two adjacent characters (act → cat);
     *
     * To find similar terms, the fuzzy query creates a set of all possible variations, or expansions, of the search term within a specified edit distance. The query then returns exact matches for each expansion.
 *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function fuzzy($field, $value = null): static
    {
        return $this->addQuery(Fuzzy::create($field, $value));
    }

    /**
     * Returns documents that contain an indexed value for a field.
     *
     * An indexed value may not exist for a document’s field due to a variety of reasons:
     *
     * The field in the source JSON is null or [];
     * The field has "index" : false and "doc_values" : false set in the mapping;
     * The length of the field value exceeded an ignore_above setting in the mapping;
     * The field value was malformed and ignore_malformed was defined in the mapping;
     *
     * @param mixed $field
     * @return $this
     */
    public function exists($field): static
    {
        return $this->addQuery(Exists::create($field));
    }

    /**
     * Returns documents based on their IDs. This query uses document IDs stored in the _id field.
     *
     * @param mixed $value
     * @return $this
     */
    public function ids($value): static
    {
        if (is_array($value) && !isset($value['values'])) {
            $value = ['values' => $value];
        }
        return $this->addQuery(IDs::create($value));
    }

    /**
     * Returns documents that contain a specific prefix in a provided field.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function prefix($field, $value = null): static
    {
        return $this->addQuery(Prefix::create($field, $value));
    }

    /**
     * Returns documents that contain terms within a provided range.
     *
     * @example $query->range('price', function (Range $r) { $r->gte(10)->lte(50) })
     * @example $query->range('price', ['gte' => 10, 'lte' => 50])
     *
     * @param string|Range $field
     * @param callable|array<string, mixed> $value
     * @return $this
     */
    public function range($field, $value = null): static
    {
        return $this->addQuery(Range::create($field, $value));
    }

    /**
     * Returns documents that contain a specific prefix in a provided field.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function regexp($field, $value = null): static
    {
        return $this->addQuery(Regexp::create($field, $value));
    }

    /**
     * Returns documents that contain an exact term in a provided field.
     *
     * @example $query->term('status', function (Term $t) { $t->value('published') })
     *
     * @param string|Term $field
     * @param callable|string|array<string, mixed> $value
     * @return $this
     */
    public function term($field, $value = null): static
    {
        return $this->addQuery(Term::create($field, $value));
    }

    /**
     * Returns documents that contain one or more exact terms in a provided field.
     *
     * The terms query is the same as the term query, except you can search for multiple values. A document will match if it contains at least one of the terms. To search for documents that contain more than one matching term, use the terms_set query.
     *
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function terms($field, $value = null): static
    {
        return $this->addQuery(Terms::create($field, $value));
    }

    /**
     * Returns documents that contain a minimum number of exact terms in a provided field.
     *
     * The terms_set query is the same as the terms query, except you can define the number of matching terms required to return a document. For example:
     *
     * A field, programming_languages, contains a list of known programming languages, such as c++, java, or php for job candidates. You can use the terms_set query to return documents that match at least two of these languages.
     * A field, permissions, contains a list of possible user permissions for an application. You can use the terms_set query to return documents that match a subset of these permissions.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function termsSet($field, $value = null): static
    {
        return $this->addQuery(TermsSet::create($field, $value));
    }

    /**
     * Returns documents that contain terms matching a wildcard pattern.
     *
     * A wildcard operator is a placeholder that matches one or more characters. For example, the * wildcard operator matches zero or more characters. You can combine wildcard operators with other characters to create a wildcard pattern.
     *
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function wildcard($field, $value = null): static
    {
        return $this->addQuery(Wildcard::create($field, $value));
    }
}

<?php

/*
 * Copyright (c) 2013 Algolia
 * http://www.algolia.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

namespace AlgoliaSearch;

/*
 * Contains all the functions related to one index
 * You should use Client.initIndex(indexName) to retrieve this object
 */
class Index
{
    /**
     * @var ClientContext
     */
    private $context;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    public $indexName;

    /**
     * @var string
     */
    private $urlIndexName;

    /**
     * Index initialization (You should not instantiate this yourself).
     *
     * @param ClientContext $context
     * @param Client        $client
     * @param string        $indexName
     *
     * @internal
     */
    public function __construct(ClientContext $context, Client $client, $indexName)
    {
        $this->context = $context;
        $this->client = $client;
        $this->indexName = $indexName;
        $this->urlIndexName = urlencode($indexName);
    }

    /**
     * Perform batch operation on several objects.
     *
     * @param array  $objects         contains an array of objects to update (each object must contains an objectID
     *                                attribute)
     * @param string $objectIDKey     the key in each object that contains the objectID
     * @param string $objectActionKey the key in each object that contains the action to perform (addObject, updateObject,
     *                                deleteObject or partialUpdateObject)
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function batchObjects($objects, $objectIDKey = 'objectID', $objectActionKey = 'objectAction')
    {
        $requests = array();
        $allowedActions = array(
            'addObject',
            'updateObject',
            'deleteObject',
            'partialUpdateObject',
            'partialUpdateObjectNoCreate',
        );

        foreach ($objects as $obj) {
            // If no or invalid action, assume updateObject
            if (!isset($obj[$objectActionKey]) || !in_array($obj[$objectActionKey], $allowedActions)) {
                throw new \Exception('invalid or no action detected');
            }

            $action = $obj[$objectActionKey];

            // The action key is not included in the object
            unset($obj[$objectActionKey]);

            $req = array('action' => $action, 'body' => $obj);

            if (array_key_exists($objectIDKey, $obj)) {
                $req['objectID'] = (string) $obj[$objectIDKey];
            }

            $requests[] = $req;
        }

        return $this->batch(array('requests' => $requests));
    }

    /**
     * Add an object in this index.
     *
     * @param array       $content  contains the object to add inside the index.
     *                              The object is represented by an associative array
     * @param string|null $objectID (optional) an objectID you want to attribute to this object
     *                              (if the attribute already exist the old object will be overwrite)
     *
     * @return mixed
     */
    public function addObject($content, $objectID = null)
    {
        if ($objectID === null) {
            return $this->client->request(
                $this->context,
                'POST',
                '/1/indexes/'.$this->urlIndexName,
                array(),
                $content,
                $this->context->writeHostsArray,
                $this->context->connectTimeout,
                $this->context->readTimeout
            );
        }

        return $this->client->request(
            $this->context,
            'PUT',
            '/1/indexes/'.$this->urlIndexName.'/'.urlencode($objectID),
            array(),
            $content,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Add several objects.
     *
     * @param array  $objects     contains an array of objects to add. If the object contains an objectID
     * @param string $objectIDKey
     *
     * @return mixed
     */
    public function addObjects($objects, $objectIDKey = 'objectID')
    {
        $requests = $this->buildBatch('addObject', $objects, true, $objectIDKey);

        return $this->batch($requests);
    }

    /**
     * Get an object from this index.
     *
     * @param string    $objectID             the unique identifier of the object to retrieve
     * @param string[]  $attributesToRetrieve (optional) if set, contains the list of attributes to retrieve
     *
     * @return mixed
     */
    public function getObject($objectID, $attributesToRetrieve = null)
    {
        $id = urlencode($objectID);
        if ($attributesToRetrieve === null) {
            return $this->client->request(
                $this->context,
                'GET',
                '/1/indexes/'.$this->urlIndexName.'/'.$id,
                null,
                null,
                $this->context->readHostsArray,
                $this->context->connectTimeout,
                $this->context->readTimeout
            );
        }

        if (is_array($attributesToRetrieve)) {
            $attributesToRetrieve = implode(',', $attributesToRetrieve);
        }

        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/'.$id,
            array('attributes' => $attributesToRetrieve),
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Get several objects from this index.
     *
     * @param array    $objectIDs            the array of unique identifier of objects to retrieve
     * @param string[] $attributesToRetrieve (optional) if set, contains the list of attributes to retrieve
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getObjects($objectIDs, $attributesToRetrieve = null)
    {
        if ($objectIDs == null) {
            throw new \Exception('No list of objectID provided');
        }

        $requests = array();
        foreach ($objectIDs as $object) {
            $req = array('indexName' => $this->indexName, 'objectID' => $object);

            if ($attributesToRetrieve) {
                if (is_array($attributesToRetrieve)) {
                    $attributesToRetrieve = implode(',', $attributesToRetrieve);
                }

                $req['attributesToRetrieve'] = $attributesToRetrieve;
            }

            array_push($requests, $req);
        }

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/*/objects',
            array(),
            array('requests' => $requests),
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Update partially an object (only update attributes passed in argument).
     *
     * @param array $partialObject     contains the object attributes to override, the
     *                                 object must contains an objectID attribute
     * @param bool  $createIfNotExists
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function partialUpdateObject($partialObject, $createIfNotExists = true)
    {
        $queryString = $createIfNotExists ? '' : '?createIfNotExists=false';

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/'.urlencode($partialObject['objectID']).'/partial'.$queryString,
            array(),
            $partialObject,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Partially Override the content of several objects.
     *
     * @param array  $objects           contains an array of objects to update (each object must contains a objectID attribute)
     * @param string $objectIDKey
     * @param bool   $createIfNotExists
     *
     * @return mixed
     */
    public function partialUpdateObjects($objects, $objectIDKey = 'objectID', $createIfNotExists = true)
    {
        if ($createIfNotExists) {
            $requests = $this->buildBatch('partialUpdateObject', $objects, true, $objectIDKey);
        } else {
            $requests = $this->buildBatch('partialUpdateObjectNoCreate', $objects, true, $objectIDKey);
        }

        return $this->batch($requests);
    }

    /**
     * Override the content of object.
     *
     * @param array  $object      contains the object to save, the object must contains an objectID attribute
     *                            or attribute specified in $objectIDKey considered as objectID
     * @param string $objectIDKey
     *
     * @return mixed
     */
    public function saveObject($object, $objectIDKey = 'objectID')
    {
        return $this->client->request(
            $this->context,
            'PUT',
            '/1/indexes/'.$this->urlIndexName.'/'.urlencode($object[$objectIDKey]),
            array(),
            $object,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Override the content of several objects.
     *
     * @param array  $objects     contains an array of objects to update (each object must contains a objectID attribute)
     * @param string $objectIDKey
     *
     * @return mixed
     */
    public function saveObjects($objects, $objectIDKey = 'objectID')
    {
        $requests = $this->buildBatch('updateObject', $objects, true, $objectIDKey);

        return $this->batch($requests);
    }

    /**
     * Delete an object from the index.
     *
     * @param int|string $objectID the unique identifier of object to delete
     *
     * @return mixed
     *
     * @throws AlgoliaException
     * @throws \Exception
     */
    public function deleteObject($objectID)
    {
        if ($objectID == null || mb_strlen($objectID) == 0) {
            throw new \Exception('objectID is mandatory');
        }

        return $this->client->request(
            $this->context,
            'DELETE',
            '/1/indexes/'.$this->urlIndexName.'/'.urlencode($objectID),
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Delete several objects.
     *
     * @param array $objects contains an array of objectIDs to delete. If the object contains an objectID
     *
     * @return mixed
     */
    public function deleteObjects($objects)
    {
        $objectIDs = array();
        foreach ($objects as $key => $id) {
            $objectIDs[$key] = array('objectID' => $id);
        }
        $requests = $this->buildBatch('deleteObject', $objectIDs, true);

        return $this->batch($requests);
    }

    /**
     * Delete all objects matching a query.
     *
     * @param string $query        the query string
     * @param array  $args         the optional query parameters
     * @param bool   $waitLastCall
     *                             /!\ Be safe with "waitLastCall"
     *                             In really rare cases you can have the number of hits smaller than the hitsPerPage
     *                             param if you trigger the timeout of the search, in that case you won't remove all
     *                             the records
     *
     * @return int the number of delete operations
     */
    public function deleteByQuery($query, $args = array(), $waitLastCall = true)
    {
        $args['attributesToRetrieve'] = 'objectID';
        $args['hitsPerPage'] = 1000;
        $args['distinct'] = false;

        $deletedCount = 0;
        $results = $this->search($query, $args);
        while ($results['nbHits'] != 0) {
            $objectIDs = array();
            foreach ($results['hits'] as $elt) {
                array_push($objectIDs, $elt['objectID']);
            }
            $res = $this->deleteObjects($objectIDs);
            $deletedCount += count($objectIDs);
            if ($results['nbHits'] < $args['hitsPerPage'] && false === $waitLastCall) {
                break;
            }
            $this->waitTask($res['taskID']);
            $results = $this->search($query, $args);
        }

        return $deletedCount;
    }

    /**
     * Search inside the index.
     *
     * @param string $query the full text query
     * @param mixed $args (optional) if set, contains an associative array with query parameters:
     *                      - page: (integer) Pagination parameter used to select the page to retrieve.
     *                      Page is zero-based and defaults to 0. Thus, to retrieve the 10th page you need to set page=9
     *                      - hitsPerPage: (integer) Pagination parameter used to select the number of hits per page.
     *                      Defaults to 20.
     *                      - attributesToRetrieve: a string that contains the list of object attributes you want to
     *                      retrieve (let you minimize the answer size). Attributes are separated with a comma (for
     *                      example "name,address"). You can also use a string array encoding (for example
     *                      ["name","address"]). By default, all attributes are retrieved. You can also use '*' to
     *                      retrieve all values when an attributesToRetrieve setting is specified for your index.
     *                      - attributesToHighlight: a string that contains the list of attributes you want to highlight
     *                      according to the query. Attributes are separated by a comma. You can also use a string array
     *                      encoding (for example ["name","address"]). If an attribute has no match for the query, the raw
     *                      value is returned. By default all indexed text attributes are highlighted. You can use `*` if
     *                      you want to highlight all textual attributes. Numerical attributes are not highlighted. A
     *                      matchLevel is returned for each highlighted attribute and can contain:
     *                      - full: if all the query terms were found in the attribute,
     *                      - partial: if only some of the query terms were found,
     *                      - none: if none of the query terms were found.
     *                      - attributesToSnippet: a string that contains the list of attributes to snippet alongside the
     *                      number of words to return (syntax is `attributeName:nbWords`). Attributes are separated by a
     *                      comma (Example: attributesToSnippet=name:10,content:10). You can also use a string array
     *                      encoding (Example: attributesToSnippet: ["name:10","content:10"]). By default no snippet is
     *                      computed.
     *                      - minWordSizefor1Typo: the minimum number of characters in a query word to accept one typo in
     *                      this word. Defaults to 3.
     *                      - minWordSizefor2Typos: the minimum number of characters in a query word to accept two typos
     *                      in this word. Defaults to 7.
     *                      - getRankingInfo: if set to 1, the result hits will contain ranking information in
     *                      _rankingInfo attribute.
     *                      - aroundLatLng: search for entries around a given latitude/longitude (specified as two floats
     *                      separated by a comma). For example aroundLatLng=47.316669,5.016670). You can specify the
     *                      maximum distance in meters with the aroundRadius parameter (in meters) and the precision for
     *                      ranking with aroundPrecision
     *                      (for example if you set aroundPrecision=100, two objects that are distant of less than 100m
     *                      will be considered as identical for "geo" ranking parameter). At indexing, you should specify
     *                      geoloc of an object with the _geoloc attribute (in the form {"_geoloc":{"lat":48.853409,
     *                      "lng":2.348800}})
     *                      - insideBoundingBox: search entries inside a given area defined by the two extreme points of a
     *                      rectangle (defined by 4 floats: p1Lat,p1Lng,p2Lat,p2Lng). For example
     *                      insideBoundingBox=47.3165,4.9665,47.3424,5.0201). At indexing, you should specify geoloc of an
     *                      object with the _geoloc attribute (in the form {"_geoloc":{"lat":48.853409, "lng":2.348800}})
     *                      - numericFilters: a string that contains the list of numeric filters you want to apply
     *                      separated by a comma. The syntax of one filter is `attributeName` followed by `operand`
     *                      followed by `value`. Supported operands are `<`, `<=`, `=`, `>` and `>=`. You can have
     *                      multiple conditions on one attribute like for example numericFilters=price>100,price<1000. You
     *                      can also use a string array encoding (for example numericFilters: ["price>100","price<1000"]).
     *                      - tagFilters: filter the query by a set of tags. You can AND tags by separating them by
     *                      commas.
     *                      To OR tags, you must add parentheses. For example, tags=tag1,(tag2,tag3) means tag1 AND (tag2
     *                      OR tag3). You can also use a string array encoding, for example tagFilters:
     *                      ["tag1",["tag2","tag3"]] means tag1 AND (tag2 OR tag3). At indexing, tags should be added in
     *                      the _tags** attribute of objects (for example {"_tags":["tag1","tag2"]}).
     *                      - facetFilters: filter the query by a list of facets.
     *                      Facets are separated by commas and each facet is encoded as `attributeName:value`.
     *                      For example: `facetFilters=category:Book,author:John%20Doe`.
     *                      You can also use a string array encoding (for example
     *                      `["category:Book","author:John%20Doe"]`).
     *                      - facets: List of object attributes that you want to use for faceting.
     *                      Attributes are separated with a comma (for example `"category,author"` ).
     *                      You can also use a JSON string array encoding (for example ["category","author"]).
     *                      Only attributes that have been added in **attributesForFaceting** index setting can be used in
     *                      this parameter. You can also use `*` to perform faceting on all attributes specified in
     *                      **attributesForFaceting**.
     *                      - queryType: select how the query words are interpreted, it can be one of the following value:
     *                      - prefixAll: all query words are interpreted as prefixes,
     *                      - prefixLast: only the last word is interpreted as a prefix (default behavior),
     *                      - prefixNone: no query word is interpreted as a prefix. This option is not recommended.
     *                      - optionalWords: a string that contains the list of words that should be considered as
     *                      optional when found in the query. The list of words is comma separated.
     *                      - distinct: If set to 1, enable the distinct feature (disabled by default) if the
     *                      attributeForDistinct index setting is set. This feature is similar to the SQL "distinct"
     *                      keyword: when enabled in a query with the distinct=1 parameter, all hits containing a
     *                      duplicate value for the attributeForDistinct attribute are removed from results. For example,
     *                      if the chosen attribute is show_name and several hits have the same value for show_name, then
     *                      only the best one is kept and others are removed.
     * @return mixed
     * @throws AlgoliaException
     */
    public function search($query, $args = null)
    {
        if ($args === null) {
            $args = array();
        }
        $args['query'] = $query;

        if (isset($args['disjunctiveFacets'])) {
            return $this->searchWithDisjunctiveFaceting($query, $args);
        }

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/query',
            array(),
            array('params' => $this->client->buildQuery($args)),
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->searchTimeout
        );
    }

    /**
     * @param $query
     * @param $args
     * @return mixed
     * @throws AlgoliaException
     */
    private function searchWithDisjunctiveFaceting($query, $args)
    {
        if (! is_array($args['disjunctiveFacets']) || count($args['disjunctiveFacets']) <= 0) {
            throw new \InvalidArgumentException('disjunctiveFacets needs to be an non empty array');
        }

        if (isset($args['filters'])) {
            throw new \InvalidArgumentException('You can not use disjunctive faceting and the filters parameter');
        }

        /**
         * Prepare queries
         */
        // Get the list of disjunctive queries to do: 1 per disjunctive facet
        $disjunctiveQueries = $this->getDisjunctiveQueries($args);

        // Format disjunctive queries for multipleQueries call
        foreach ($disjunctiveQueries as &$disjunctiveQuery) {
            $disjunctiveQuery['indexName'] = $this->indexName;
            $disjunctiveQuery['query'] = $query;
            unset($disjunctiveQuery['disjunctiveFacets']);
        }

        // Merge facets and disjunctiveFacets for the hits query
        $facets = isset($args['facets']) ? $args['facets'] : array();
        $facets = array_merge($facets, $args['disjunctiveFacets']);
        unset($args['disjunctiveFacets']);

        // format the hits query for multipleQueries call
        $args['query'] = $query;
        $args['indexName'] = $this->indexName;
        $args['facets'] = $facets;

        // Put the hit query first
        array_unshift($disjunctiveQueries, $args);

        /**
         * Do all queries in one call
         */
        $results = $this->client->multipleQueries(array_values($disjunctiveQueries));
        $results = $results['results'];

        /**
         * Merge facets from disjunctive queries with facets from the hits query
         */

        // The first query is the hits query that the one we'll return to the user
        $queryResults = array_shift($results);

        // To be able to add facets from disjunctive query we create 'facets' key in case we only have disjunctive facets
        if (false === isset($queryResults['facets'])) {
            $queryResults['facets'] = array();
        }

        foreach ($results as $disjunctiveResults) {
            if (isset($disjunctiveResults['facets'])) {
                foreach ($disjunctiveResults['facets'] as $facetName => $facetValues) {
                    $queryResults['facets'][$facetName] = $facetValues;
                }
            }
        }

        return $queryResults;
    }

    /**
     * @param $queryParams
     * @return array
     */
    private function getDisjunctiveQueries($queryParams)
    {
        $queriesParams = array();

        foreach ($queryParams['disjunctiveFacets'] as $facetName) {
            $params = $queryParams;
            $params['facets'] = array($facetName);
            $facetFilters = isset($params['facetFilters']) ? $params['facetFilters']: array();
            $numericFilters = isset($params['numericFilters']) ? $params['numericFilters']: array();

            $additionalParams = array(
                'hitsPerPage' => 1,
                'page' => 0,
                'attributesToRetrieve' => array(),
                'attributesToHighlight' => array(),
                'attributesToSnippet' => array()
            );

            $additionalParams['facetFilters'] = $this->getAlgoliaFiltersArrayWithoutCurrentRefinement($facetFilters, $facetName . ':');
            $additionalParams['numericFilters'] = $this->getAlgoliaFiltersArrayWithoutCurrentRefinement($numericFilters, $facetName);

            $queriesParams[$facetName] = array_merge($params, $additionalParams);
        }

        return $queriesParams;
    }

    /**
     * @param $filters
     * @param $needle
     * @return array
     */
    private function getAlgoliaFiltersArrayWithoutCurrentRefinement($filters, $needle)
    {
        // iterate on each filters which can be string or array and filter out every refinement matching the needle
        for ($i = 0; $i < count($filters); $i++) {
            if (is_array($filters[$i])) {
                foreach ($filters[$i] as $filter) {
                    if (mb_substr($filter, 0, mb_strlen($needle)) === $needle) {
                        unset($filters[$i]);
                        $filters = array_values($filters);
                        $i--;
                        break;
                    }
                }
            } else {
                if (mb_substr($filters[$i], 0, mb_strlen($needle)) === $needle) {
                    unset($filters[$i]);
                    $filters = array_values($filters);
                    $i--;
                }
            }
        }

        return $filters;
    }

    /**
     * Perform a search inside facets.
     *
     * @param $facetName
     * @param $facetQuery
     * @param array $query
     *
     * @return mixed
     */
    public function searchForFacetValues($facetName, $facetQuery, $query = array())
    {
        $query['facetQuery'] = $facetQuery;

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/facets/'.$facetName.'/query',
            array(),
            array('params' => $this->client->buildQuery($query)),
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->searchTimeout
        );
    }

    /**
     * Perform a search with disjunctive facets generating as many queries as number of disjunctive facets.
     *
     * @param string $query              the query
     * @param array  $disjunctive_facets the array of disjunctive facets
     * @param array  $params             a hash representing the regular query parameters
     * @param array  $refinements        a hash ("string" -> ["array", "of", "refined", "values"]) representing the current refinements
     *                                   ex: { "my_facet1" => ["my_value1", ["my_value2"], "my_disjunctive_facet1" => ["my_value1", "my_value2"] }
     *
     * @return mixed
     *
     * @throws AlgoliaException
     * @throws \Exception
     * @deprecated you should use $index->search($query, ['disjunctiveFacets' => $disjunctive_facets]]); instead
     */
    public function searchDisjunctiveFaceting($query, $disjunctive_facets, $params = array(), $refinements = array())
    {
        if (gettype($disjunctive_facets) != 'string' && gettype($disjunctive_facets) != 'array') {
            throw new AlgoliaException('Argument "disjunctive_facets" must be a String or an Array');
        }

        if (gettype($refinements) != 'array') {
            throw new AlgoliaException('Argument "refinements" must be a Hash of Arrays');
        }

        if (gettype($disjunctive_facets) == 'string') {
            $disjunctive_facets = explode(',', $disjunctive_facets);
        }

        $disjunctive_refinements = array();
        foreach ($refinements as $key => $value) {
            if (in_array($key, $disjunctive_facets)) {
                $disjunctive_refinements[$key] = $value;
            }
        }
        $queries = array();
        $filters = array();

        foreach ($refinements as $key => $value) {
            $r = array_map(
                function ($val) use ($key) {
                    return $key.':'.$val;
                },
                $value
            );

            if (in_array($key, $disjunctive_refinements)) {
                $filter = array_merge($filters, $r);
            } else {
                array_push($filters, $r);
            }
        }
        $params['indexName'] = $this->indexName;
        $params['query'] = $query;
        $params['facetFilters'] = $filters;
        array_push($queries, $params);
        foreach ($disjunctive_facets as $disjunctive_facet) {
            $filters = array();
            foreach ($refinements as $key => $value) {
                if ($key != $disjunctive_facet) {
                    $r = array_map(
                        function ($val) use ($key) {
                            return $key.':'.$val;
                        },
                        $value
                    );

                    if (in_array($key, $disjunctive_refinements)) {
                        $filter = array_merge($filters, $r);
                    } else {
                        array_push($filters, $r);
                    }
                }
            }
            $params['indexName'] = $this->indexName;
            $params['query'] = $query;
            $params['facetFilters'] = $filters;
            $params['page'] = 0;
            $params['hitsPerPage'] = 0;
            $params['attributesToRetrieve'] = array();
            $params['attributesToHighlight'] = array();
            $params['attributesToSnippet'] = array();
            $params['facets'] = $disjunctive_facet;
            $params['analytics'] = false;
            array_push($queries, $params);
        }
        $answers = $this->client->multipleQueries($queries);

        $aggregated_answer = $answers['results'][0];
        $aggregated_answer['disjunctiveFacets'] = array();
        for ($i = 1; $i < count($answers['results']); $i++) {
            foreach ($answers['results'][$i]['facets'] as $key => $facet) {
                $aggregated_answer['disjunctiveFacets'][$key] = $facet;
                if (!in_array($key, $disjunctive_refinements)) {
                    continue;
                }
                foreach ($disjunctive_refinements[$key] as $r) {
                    if (is_null($aggregated_answer['disjunctiveFacets'][$key][$r])) {
                        $aggregated_answer['disjunctiveFacets'][$key][$r] = 0;
                    }
                }
            }
        }

        return $aggregated_answer;
    }

    /**
     * Browse all index content.
     *
     * @param int $page        Pagination parameter used to select the page to retrieve.
     *                         Page is zero-based and defaults to 0. Thus, to retrieve the 10th page you need to set page=9
     * @param int $hitsPerPage : Pagination parameter used to select the number of hits per page. Defaults to 1000.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    private function doBcBrowse($page = 0, $hitsPerPage = 1000)
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/browse',
            array('page' => $page, 'hitsPerPage' => $hitsPerPage),
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Wait the publication of a task on the server.
     * All server task are asynchronous and you can check with this method that the task is published.
     *
     * @param string $taskID          the id of the task returned by server
     * @param int    $timeBeforeRetry the time in milliseconds before retry (default = 100ms)
     *
     * @return mixed
     */
    public function waitTask($taskID, $timeBeforeRetry = 100)
    {
        while (true) {
            $res = $this->getTaskStatus($taskID);
            if ($res['status'] === 'published') {
                return $res;
            }
            usleep($timeBeforeRetry * 1000);
        }
    }

    /**
     * get the status of a task on the server.
     * All server task are asynchronous and you can check with this method that the task is published or not.
     *
     * @param string $taskID the id of the task returned by server
     *
     * @return mixed
     */
    public function getTaskStatus($taskID)
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/task/'.$taskID,
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Get settings of this index.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function getSettings()
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/settings?getVersion=2',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * This function deletes the index content. Settings and index specific API keys are kept untouched.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function clearIndex()
    {
        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/clear',
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Set settings for this index.
     *
     * @param mixed $settings          the settings object that can contains :
     *                                 - minWordSizefor1Typo: (integer) the minimum number of characters to accept one typo (default =
     *                                 3).
     *                                 - minWordSizefor2Typos: (integer) the minimum number of characters to accept two typos (default
     *                                 = 7).
     *                                 - hitsPerPage: (integer) the number of hits per page (default = 10).
     *                                 - attributesToRetrieve: (array of strings) default list of attributes to retrieve in objects.
     *                                 If set to null, all attributes are retrieved.
     *                                 - attributesToHighlight: (array of strings) default list of attributes to highlight.
     *                                 If set to null, all indexed attributes are highlighted.
     *                                 - attributesToSnippet**: (array of strings) default list of attributes to snippet alongside the
     *                                 number of words to return (syntax is attributeName:nbWords). By default no snippet is computed.
     *                                 If set to null, no snippet is computed.
     *                                 - searchableAttributes (formerly named attributesToIndex): (array of strings) the list of fields you want to index.
     *                                 If set to null, all textual and numerical attributes of your objects are indexed, but you
     *                                 should update it to get optimal results. This parameter has two important uses:
     *                                 - Limit the attributes to index: For example if you store a binary image in base64, you want to
     *                                 store it and be able to retrieve it but you don't want to search in the base64 string.
     *                                 - Control part of the ranking*: (see the ranking parameter for full explanation) Matches in
     *                                 attributes at the beginning of the list will be considered more important than matches in
     *                                 attributes further down the list. In one attribute, matching text at the beginning of the
     *                                 attribute will be considered more important than text after, you can disable this behavior if
     *                                 you add your attribute inside `unordered(AttributeName)`, for example searchableAttributes:
     *                                 ["title", "unordered(text)"].
     *                                 - attributesForFaceting: (array of strings) The list of fields you want to use for faceting.
     *                                 All strings in the attribute selected for faceting are extracted and added as a facet. If set
     *                                 to null, no attribute is used for faceting.
     *                                 - attributeForDistinct: (string) The attribute name used for the Distinct feature. This feature
     *                                 is similar to the SQL "distinct" keyword: when enabled in query with the distinct=1 parameter,
     *                                 all hits containing a duplicate value for this attribute are removed from results. For example,
     *                                 if the chosen attribute is show_name and several hits have the same value for show_name, then
     *                                 only the best one is kept and others are removed.
     *                                 - ranking: (array of strings) controls the way results are sorted.
     *                                 We have six available criteria:
     *                                 - typo: sort according to number of typos,
     *                                 - geo: sort according to decreasing distance when performing a geo-location based search,
     *                                 - proximity: sort according to the proximity of query words in hits,
     *                                 - attribute: sort according to the order of attributes defined by searchableAttributes,
     *                                 - exact:
     *                                 - if the user query contains one word: sort objects having an attribute that is exactly the
     *                                 query word before others. For example if you search for the "V" TV show, you want to find it
     *                                 with the "V" query and avoid to have all popular TV show starting by the v letter before it.
     *                                 - if the user query contains multiple words: sort according to the number of words that matched
     *                                 exactly (and not as a prefix).
     *                                 - custom: sort according to a user defined formula set in **customRanking** attribute.
     *                                 The standard order is ["typo", "geo", "proximity", "attribute", "exact", "custom"]
     *                                 - customRanking: (array of strings) lets you specify part of the ranking.
     *                                 The syntax of this condition is an array of strings containing attributes prefixed by asc
     *                                 (ascending order) or desc (descending order) operator. For example `"customRanking" =>
     *                                 ["desc(population)", "asc(name)"]`
     *                                 - queryType: Select how the query words are interpreted, it can be one of the following value:
     *                                 - prefixAll: all query words are interpreted as prefixes,
     *                                 - prefixLast: only the last word is interpreted as a prefix (default behavior),
     *                                 - prefixNone: no query word is interpreted as a prefix. This option is not recommended.
     *                                 - highlightPreTag: (string) Specify the string that is inserted before the highlighted parts in
     *                                 the query result (default to "<em>").
     *                                 - highlightPostTag: (string) Specify the string that is inserted after the highlighted parts in
     *                                 the query result (default to "</em>").
     *                                 - optionalWords: (array of strings) Specify a list of words that should be considered as
     *                                 optional when found in the query.
     * @param bool  $forwardToReplicas
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function setSettings($settings, $forwardToReplicas = false)
    {
        $url = '/1/indexes/'.$this->urlIndexName.'/settings';

        if ($forwardToReplicas) {
            $url = $url.'?forwardToReplicas=true';
        }

        return $this->client->request(
            $this->context,
            'PUT',
            $url,
            array(),
            $settings,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * List all existing API keys associated to this index with their associated ACLs.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function listApiKeys()
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/keys',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @deprecated use listApiKeys instead
     * @return mixed
     */
    public function listUserKeys()
    {
        return $this->listApiKeys();
    }

    /**
     * @deprecated use getApiKey in
     * @param $key
     * @return mixed
     */
    public function getUserKeyACL($key)
    {
        return $this->getApiKey($key);
    }

    /**
     * Get ACL of a API key associated to this index.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function getApiKey($key)
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/keys/'.$key,
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }


    /**
     * Delete an existing API key associated to this index.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function deleteApiKey($key)
    {
        return $this->client->request(
            $this->context,
            'DELETE',
            '/1/indexes/'.$this->urlIndexName.'/keys/'.$key,
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $key
     * @return mixed
     * @deprecated use deleteApiKey instead
     */
    public function deleteUserKey($key)
    {
        return $this->deleteApiKey($key);
    }

    /**
     * Create a new API key associated to this index.
     *
     * @param array $obj                    can be two different parameters:
     *                                      The list of parameters for this key. Defined by a array that
     *                                      can contains the following values:
     *                                      - acl: array of string
     *                                      - indices: array of string
     *                                      - validity: int
     *                                      - referers: array of string
     *                                      - description: string
     *                                      - maxHitsPerQuery: integer
     *                                      - queryParameters: string
     *                                      - maxQueriesPerIPPerHour: integer
     *                                      Or the list of ACL for this key. Defined by an array of NSString that
     *                                      can contains the following values:
     *                                      - search: allow to search (https and http)
     *                                      - addObject: allows to add/update an object in the index (https only)
     *                                      - deleteObject : allows to delete an existing object (https only)
     *                                      - deleteIndex : allows to delete index content (https only)
     *                                      - settings : allows to get index settings (https only)
     *                                      - editSettings : allows to change index settings (https only)
     * @param int   $validity               the number of seconds after which the key will be automatically removed (0 means
     *                                      no time limit for this key)
     * @param int   $maxQueriesPerIPPerHour Specify the maximum number of API calls allowed from an IP address per hour.
     *                                      Defaults to 0 (no rate limit).
     * @param int   $maxHitsPerQuery        Specify the maximum number of hits this API key can retrieve in one call.
     *                                      Defaults to 0 (unlimited)
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function addApiKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)
    {
        // is dict of value
        if ($obj !== array_values($obj)) {
            $params = $obj;
            $params['validity'] = $validity;
            $params['maxQueriesPerIPPerHour'] = $maxQueriesPerIPPerHour;
            $params['maxHitsPerQuery'] = $maxHitsPerQuery;
        } else {
            $params = array(
                'acl'                    => $obj,
                'validity'               => $validity,
                'maxQueriesPerIPPerHour' => $maxQueriesPerIPPerHour,
                'maxHitsPerQuery'        => $maxHitsPerQuery,
            );
        }

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/keys',
            array(),
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $obj
     * @param int $validity
     * @param int $maxQueriesPerIPPerHour
     * @param int $maxHitsPerQuery
     * @return mixed
     * @deprecated use addApiKey instead
     */
    public function addUserKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)
    {
        return $this->addApiKey($obj, $validity, $maxQueriesPerIPPerHour, $maxHitsPerQuery);
    }


    /**
     * Update an API key associated to this index.
     *
     * @param string $key
     * @param array  $obj                    can be two different parameters:
     *                                       The list of parameters for this key. Defined by a array that
     *                                       can contains the following values:
     *                                       - acl: array of string
     *                                       - indices: array of string
     *                                       - validity: int
     *                                       - referers: array of string
     *                                       - description: string
     *                                       - maxHitsPerQuery: integer
     *                                       - queryParameters: string
     *                                       - maxQueriesPerIPPerHour: integer
     *                                       Or the list of ACL for this key. Defined by an array of NSString that
     *                                       can contains the following values:
     *                                       - search: allow to search (https and http)
     *                                       - addObject: allows to add/update an object in the index (https only)
     *                                       - deleteObject : allows to delete an existing object (https only)
     *                                       - deleteIndex : allows to delete index content (https only)
     *                                       - settings : allows to get index settings (https only)
     *                                       - editSettings : allows to change index settings (https only)
     * @param int    $validity               the number of seconds after which the key will be automatically removed (0 means
     *                                       no time limit for this key)
     * @param int    $maxQueriesPerIPPerHour Specify the maximum number of API calls allowed from an IP address per hour.
     *                                       Defaults to 0 (no rate limit).
     * @param int    $maxHitsPerQuery        Specify the maximum number of hits this API key can retrieve in one call.
     *                                       Defaults to 0 (unlimited)
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function updateApiKey($key, $obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)
    {
        // is dict of value
        if ($obj !== array_values($obj)) {
            $params = $obj;
            $params['validity'] = $validity;
            $params['maxQueriesPerIPPerHour'] = $maxQueriesPerIPPerHour;
            $params['maxHitsPerQuery'] = $maxHitsPerQuery;
        } else {
            $params = array(
                'acl'                    => $obj,
                'validity'               => $validity,
                'maxQueriesPerIPPerHour' => $maxQueriesPerIPPerHour,
                'maxHitsPerQuery'        => $maxHitsPerQuery,
            );
        }

        return $this->client->request(
            $this->context,
            'PUT',
            '/1/indexes/'.$this->urlIndexName.'/keys/'.$key,
            array(),
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $key
     * @param $obj
     * @param int $validity
     * @param int $maxQueriesPerIPPerHour
     * @param int $maxHitsPerQuery
     * @return mixed
     * @deprecated use updateApiKey instead
     */
    public function updateUserKey($key, $obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0)
    {
        return $this->updateApiKey($key, $obj, $validity, $maxQueriesPerIPPerHour, $maxHitsPerQuery);
    }

    /**
     * Send a batch request.
     *
     * @param array $requests an associative array defining the batch request body
     *
     * @return mixed
     */
    public function batch($requests)
    {
        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/batch',
            array(),
            $requests,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Build a batch request.
     *
     * @param string $action       the batch action
     * @param array  $objects      the array of objects
     * @param string $withObjectID set an 'objectID' attribute
     * @param string $objectIDKey  the objectIDKey
     *
     * @return array
     */
    private function buildBatch($action, $objects, $withObjectID, $objectIDKey = 'objectID')
    {
        $requests = array();
        foreach ($objects as $obj) {
            $req = array('action' => $action, 'body' => $obj);
            if ($withObjectID && array_key_exists($objectIDKey, $obj)) {
                $req['objectID'] = (string) $obj[$objectIDKey];
            }
            array_push($requests, $req);
        }

        return array('requests' => $requests);
    }

    /**
     * @param string     $query
     * @param array|null $params
     *
     * @return IndexBrowser
     */
    private function doBrowse($query, $params = null)
    {
        return new IndexBrowser($this, $query, $params);
    }

    /**
     * @param string     $query
     * @param array|null $params
     * @param $cursor
     *
     * @return mixed
     */
    public function browseFrom($query, $params = null, $cursor = null)
    {
        if ($params === null) {
            $params = array();
        }
        foreach ($params as $key => $value) {
            if (gettype($value) == 'array') {
                $params[$key] = Json::encode($value);
            }
        }
        if ($query != null) {
            $params['query'] = $query;
        }
        if ($cursor != null) {
            $params['cursor'] = $cursor;
        }

        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/browse',
            $params,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $query
     * @param $synonymType
     * @param null $page
     * @param null $hitsPerPage
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function searchSynonyms($query, array $synonymType = array(), $page = null, $hitsPerPage = null)
    {
        $params = array();

        if ($query !== null) {
            $params['query'] = $query;
        }

        if (count($synonymType) > 0) {
            $types = array();

            foreach ($synonymType as $type) {
                if (is_integer($type)) {
                    $types[] = SynonymType::getSynonymsTypeString($type);
                } else {
                    $types[] = $type;
                }
            }
            $params['type'] = implode(',', $types);
        }

        if ($page !== null) {
            $params['page'] = $page;
        }

        if ($hitsPerPage !== null) {
            $params['hitsPerPage'] = $hitsPerPage;
        }

        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/search',
            null,
            $params,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $objectID
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function getSynonym($objectID)
    {
        return $this->client->request(
            $this->context,
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/'.urlencode($objectID),
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $objectID
     * @param $forwardToReplicas
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function deleteSynonym($objectID, $forwardToReplicas = false)
    {
        return $this->client->request(
            $this->context,
            'DELETE',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/'.urlencode($objectID).'?forwardToReplicas='.($forwardToReplicas ? 'true' : 'false'),
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param bool $forwardToReplicas
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function clearSynonyms($forwardToReplicas = false)
    {
        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/clear?forwardToReplicas='.($forwardToReplicas ? 'true' : 'false'),
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $objects
     * @param bool $forwardToReplicas
     * @param bool $replaceExistingSynonyms
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function batchSynonyms($objects, $forwardToReplicas = false, $replaceExistingSynonyms = false)
    {
        return $this->client->request(
            $this->context,
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/batch?replaceExistingSynonyms='.($replaceExistingSynonyms ? 'true' : 'false')
                .'&forwardToReplicas='.($forwardToReplicas ? 'true' : 'false'),
            null,
            $objects,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @param $objectID
     * @param $content
     * @param bool $forwardToReplicas
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function saveSynonym($objectID, $content, $forwardToReplicas = false)
    {
        return $this->client->request(
            $this->context,
            'PUT',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/'.urlencode($objectID).'?forwardToReplicas='.($forwardToReplicas ? 'true' : 'false'),
            null,
            $content,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * @deprecated Please use searchForFacetValues instead
     * @param $facetName
     * @param $facetQuery
     * @param array $query
     * @return mixed
     */
    public function searchFacet($facetName, $facetQuery, $query = array())
    {
        return $this->searchForFacetValues($facetName, $facetQuery, $query);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($name === 'browse') {
            if (count($arguments) >= 1 && is_string($arguments[0])) {
                return call_user_func_array(array($this, 'doBrowse'), $arguments);
            }

            return call_user_func_array(array($this, 'doBcBrowse'), $arguments);
        }

        throw new \BadMethodCallException(sprintf('No method named %s was found.', $name));
    }
}

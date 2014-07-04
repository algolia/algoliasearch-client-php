<?php

namespace AlgoliaSearch;

class Index
{
    private $indexName;
    private $client;
    private $urlIndexName;
    private $hostsArray;

    public function __construct(ClientContext $context, Client $client, $indexName)
    {
        $this->context = $context;
        $this->client = $client;
        $this->indexName = $indexName;
        $this->urlIndexName = urlencode($indexName);
    }

    /*
     * Perform batch operation on several objects
     *
     * @param objects contains an array of objects to update (each object must contains an objectID attribute)
     * @param objectIDKey  the key in each object that contains the objectID
     * @param objectActionKey  the key in each object that contains the action to perform (addObject, updateObject, deleteObject or partialUpdateObject)
     */
    public function batchObjects($objects, $objectIDKey = "objectID", $objectActionKey = "objectAction")
    {
        $requests = array();

        foreach ($objects as $obj) {
            // If no or invalid action, assume updateObject
            if (! isset($obj[$objectActionKey]) || ! in_array($obj[$objectActionKey], array('addObject', 'updateObject', 'deleteObject', 'partialUpdateObject'))) {
                $obj[$objectActionKey] = 'updateObject';
            }

            $action = $obj[$objectActionKey];
            unset($obj[$objectActionKey]); // The action key is not included in the object

            $req = array("action" => $action, "body" => $obj);

            if (array_key_exists($objectIDKey, $obj)) {
                $req["objectID"] = (string) $obj[$objectIDKey];
            }

            $requests[] = $req;
        }

        return $this->batch(array("requests" => $requests));
    }

    /*
     * Add an object in this index
     *
     * @param content contains the object to add inside the index.
     *  The object is represented by an associative array
     * @param objectID (optional) an objectID you want to attribute to this object
     * (if the attribute already exist the old object will be overwrite)
     */
    public function addObject($content, $objectID = null)
    {
        if ($objectID === null) {
            return $this->client->request($this->context, "POST", "/1/indexes/" . $this->urlIndexName, array(), $content);
        } else {
            return $this->client->request($this->context, "PUT", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($objectID), array(), $content);
        }
    }

    /*
     * Add several objects
     *
     * @param objects contains an array of objects to add. If the object contains an objectID
     */
    public function addObjects($objects, $objectIDKey = "objectID") {
        $requests = $this->buildBatch("addObject", $objects, true, $objectIDKey);
        return $this->batch($requests);
    }

    /*
     * Get an object from this index
     *
     * @param objectID the unique identifier of the object to retrieve
     * @param attributesToRetrieve (optional) if set, contains the list of attributes to retrieve as a string separated by ","
     */
    public function getObject($objectID, $attributesToRetrieve = null) {
        $id = urlencode($objectID);
        if ($attributesToRetrieve === null)
            return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/" . $id);
        else
            return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/" . $id, array("attributes" => $attributesToRetrieve));
    }

    /*
     * Update partially an object (only update attributes passed in argument)
     *
     * @param partialObject contains the object attributes to override, the
     *  object must contains an objectID attribute
     */
    public function partialUpdateObject($partialObject) {
        return $this->client->request($this->context, "POST", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($partialObject["objectID"]) . "/partial", array(), $partialObject);
    }

    /*
     * Partially Override the content of several objects
     *
     * @param objects contains an array of objects to update (each object must contains a objectID attribute)
     */
    public function partialUpdateObjects($objects, $objectIDKey = "objectID") {
        $requests = $this->buildBatch("partialUpdateObject", $objects, true, $objectIDKey);
        return $this->batch($requests);
    }

    /*
     * Override the content of object
     *
     * @param object contains the object to save, the object must contains an objectID attribute
     */
    public function saveObject($object) {
        return $this->client->request($this->context, "PUT", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($object["objectID"]), array(), $object);
    }

    /*
     * Override the content of several objects
     *
     * @param objects contains an array of objects to update (each object must contains a objectID attribute)
     */
    public function saveObjects($objects, $objectIDKey = "objectID") {
        $requests = $this->buildBatch("updateObject", $objects, true, $objectIDKey);
        return $this->batch($requests);
    }

    /*
     * Delete an object from the index
     *
     * @param objectID the unique identifier of object to delete
     */
    public function deleteObject($objectID) {
        if ($objectID == null || mb_strlen($objectID) == 0) {
            throw new \Exception('objectID is mandatory');
        }
        return $this->client->request($this->context, "DELETE", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($objectID));
    }

    /*
     * Delete several objects
     *
     * @param objects contains an array of objectIDs to delete. If the object contains an objectID
     */
    public function deleteObjects($objects) {
        $objectIDs = array();
        foreach ($objects as $key => $id) {
            $objectIDs[$key] = array('objectID' => $id);
        }
        $requests = $this->buildBatch("deleteObject", $objectIDs, true);
        return $this->batch($requests);
    }

    /*
     * Search inside the index
     *
     * @param query the full text query
     * @param args (optional) if set, contains an associative array with query parameters:
     * - page: (integer) Pagination parameter used to select the page to retrieve.
     *                   Page is zero-based and defaults to 0. Thus, to retrieve the 10th page you need to set page=9
     * - hitsPerPage: (integer) Pagination parameter used to select the number of hits per page. Defaults to 20.
     * - attributesToRetrieve: a string that contains the list of object attributes you want to retrieve (let you minimize the answer size).
     *   Attributes are separated with a comma (for example "name,address").
     *   You can also use a string array encoding (for example ["name","address"]).
     *   By default, all attributes are retrieved. You can also use '*' to retrieve all values when an attributesToRetrieve setting is specified for your index.
     * - attributesToHighlight: a string that contains the list of attributes you want to highlight according to the query.
     *   Attributes are separated by a comma. You can also use a string array encoding (for example ["name","address"]).
     *   If an attribute has no match for the query, the raw value is returned. By default all indexed text attributes are highlighted.
     *   You can use `*` if you want to highlight all textual attributes. Numerical attributes are not highlighted.
     *   A matchLevel is returned for each highlighted attribute and can contain:
     *      - full: if all the query terms were found in the attribute,
     *      - partial: if only some of the query terms were found,
     *      - none: if none of the query terms were found.
     * - attributesToSnippet: a string that contains the list of attributes to snippet alongside the number of words to return (syntax is `attributeName:nbWords`).
     *    Attributes are separated by a comma (Example: attributesToSnippet=name:10,content:10).
     *    You can also use a string array encoding (Example: attributesToSnippet: ["name:10","content:10"]). By default no snippet is computed.
     * - minWordSizefor1Typo: the minimum number of characters in a query word to accept one typo in this word. Defaults to 3.
     * - minWordSizefor2Typos: the minimum number of characters in a query word to accept two typos in this word. Defaults to 7.
     * - getRankingInfo: if set to 1, the result hits will contain ranking information in _rankingInfo attribute.
     * - aroundLatLng: search for entries around a given latitude/longitude (specified as two floats separated by a comma).
     *   For example aroundLatLng=47.316669,5.016670).
     *   You can specify the maximum distance in meters with the aroundRadius parameter (in meters) and the precision for ranking with aroundPrecision
     *   (for example if you set aroundPrecision=100, two objects that are distant of less than 100m will be considered as identical for "geo" ranking parameter).
     *   At indexing, you should specify geoloc of an object with the _geoloc attribute (in the form {"_geoloc":{"lat":48.853409, "lng":2.348800}})
     * - insideBoundingBox: search entries inside a given area defined by the two extreme points of a rectangle (defined by 4 floats: p1Lat,p1Lng,p2Lat,p2Lng).
     *   For example insideBoundingBox=47.3165,4.9665,47.3424,5.0201).
     *   At indexing, you should specify geoloc of an object with the _geoloc attribute (in the form {"_geoloc":{"lat":48.853409, "lng":2.348800}})
     * - numericFilters: a string that contains the list of numeric filters you want to apply separated by a comma.
     *   The syntax of one filter is `attributeName` followed by `operand` followed by `value`. Supported operands are `<`, `<=`, `=`, `>` and `>=`.
     *   You can have multiple conditions on one attribute like for example numericFilters=price>100,price<1000.
     *   You can also use a string array encoding (for example numericFilters: ["price>100","price<1000"]).
     * - tagFilters: filter the query by a set of tags. You can AND tags by separating them by commas.
     *   To OR tags, you must add parentheses. For example, tags=tag1,(tag2,tag3) means tag1 AND (tag2 OR tag3).
     *   You can also use a string array encoding, for example tagFilters: ["tag1",["tag2","tag3"]] means tag1 AND (tag2 OR tag3).
     *   At indexing, tags should be added in the _tags** attribute of objects (for example {"_tags":["tag1","tag2"]}).
     * - facetFilters: filter the query by a list of facets.
     *   Facets are separated by commas and each facet is encoded as `attributeName:value`.
     *   For example: `facetFilters=category:Book,author:John%20Doe`.
     *   You can also use a string array encoding (for example `["category:Book","author:John%20Doe"]`).
     * - facets: List of object attributes that you want to use for faceting.
     *   Attributes are separated with a comma (for example `"category,author"` ).
     *   You can also use a JSON string array encoding (for example ["category","author"]).
     *   Only attributes that have been added in **attributesForFaceting** index setting can be used in this parameter.
     *   You can also use `*` to perform faceting on all attributes specified in **attributesForFaceting**.
     * - queryType: select how the query words are interpreted, it can be one of the following value:
     *    - prefixAll: all query words are interpreted as prefixes,
     *    - prefixLast: only the last word is interpreted as a prefix (default behavior),
     *    - prefixNone: no query word is interpreted as a prefix. This option is not recommended.
     * - optionalWords: a string that contains the list of words that should be considered as optional when found in the query.
     *   The list of words is comma separated.
     * - distinct: If set to 1, enable the distinct feature (disabled by default) if the attributeForDistinct index setting is set.
     *   This feature is similar to the SQL "distinct" keyword: when enabled in a query with the distinct=1 parameter,
     *   all hits containing a duplicate value for the attributeForDistinct attribute are removed from results.
     *   For example, if the chosen attribute is show_name and several hits have the same value for show_name, then only the best
     *   one is kept and others are removed.
     */
    public function search($query, $args = null) {
        if ($args === null) {
            $args = array();
        }
        $args["query"] = $query;
        return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName, $args);
    }

    /*
     * Perform a search with disjunctive facets generating as many queries as number of disjunctive facets
     *
     * @param query the query
     * @param disjunctive_facets the array of disjunctive facets
     * @param params a hash representing the regular query parameters
     * @param refinements a hash ("string" -> ["array", "of", "refined", "values"]) representing the current refinements
     * ex: { "my_facet1" => ["my_value1", ["my_value2"], "my_disjunctive_facet1" => ["my_value1", "my_value2"] }
     */
    public function searchDisjunctiveFaceting($query, $disjunctive_facets, $params = array(), $refinements = array()) {
      if (gettype($disjunctive_facets) != "string" && gettype($disjunctive_facets) != "array") {
        throw new AlgoliaException("Argument \"disjunctive_facets\" must be a String or an Array");
      }
      if (gettype($refinements) != "array") {
        throw new AlgoliaException("Argument \"refinements\" must be a Hash of Arrays");
      }

      if (gettype($disjunctive_facets) == "string") {
        $disjunctive_facets = split(",", $disjunctive_facets);
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
        $r = array_map(function ($val) use ($key) { return $key . ":" . $val;}, $value);

        if (in_array($key, $disjunctive_refinements)) {
          $filter = array_merge($filters, $r);
        } else {
          array_push($filters, $r);
        }
      }
      $params["indexName"] = $this->indexName;
      $params["query"] = $query;
      $params["facetFilters"] = $filters;
      array_push($queries, $params);
      foreach ($disjunctive_facets as $disjunctive_facet) {
        $filters = array();
        foreach ($refinements as $key => $value) {
          if ($key != $disjunctive_facet) {
            $r = array_map(function($val) use($key) { return $key . ":" . $val;}, $value);

            if (in_array($key, $disjunctive_refinements)) {
              $filter = array_merge($filters, $r);
            } else {
              array_push($filters, $r);
            }
          }
        }
        $params["indexName"] = $this->indexName;
        $params["query"] = $query;
        $params["facetFilters"] = $filters;
        $params["page"] = 0;
        $params["hitsPerPage"] = 1;
        $params["facets"] = $disjunctive_facet;
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

    /*
     * Browse all index content
     *
     * @param page Pagination parameter used to select the page to retrieve.
     *             Page is zero-based and defaults to 0. Thus, to retrieve the 10th page you need to set page=9
     * @param hitsPerPage: Pagination parameter used to select the number of hits per page. Defaults to 1000.
     */
    public function browse($page = 0, $hitsPerPage = 1000) {
        return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/browse",
                                    array("page" => $page, "hitsPerPage" => $hitsPerPage));
    }

    /*
     * Wait the publication of a task on the server.
     * All server task are asynchronous and you can check with this method that the task is published.
     *
     * @param taskID the id of the task returned by server
     * @param timeBeforeRetry the time in milliseconds before retry (default = 100ms)
     */
    public function waitTask($taskID, $timeBeforeRetry = 100) {
        while (true) {
            $res = $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/task/" . $taskID);
            if ($res["status"] === "published")
                return $res;
            usleep($timeBeforeRetry * 1000);
        }
    }

    /*
     * Get settings of this index
     *
     */
    public function getSettings() {
        return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/settings");
    }

    /*
     * This function deletes the index content. Settings and index specific API keys are kept untouched.
     */
    public function clearIndex() {
        return $this->client->request($this->context, "POST", "/1/indexes/" . $this->urlIndexName . "/clear");
    }

    /*
     * Set settings for this index
     *
     * @param settigns the settings object that can contains :
     * - minWordSizefor1Typo: (integer) the minimum number of characters to accept one typo (default = 3).
     * - minWordSizefor2Typos: (integer) the minimum number of characters to accept two typos (default = 7).
     * - hitsPerPage: (integer) the number of hits per page (default = 10).
     * - attributesToRetrieve: (array of strings) default list of attributes to retrieve in objects.
     *   If set to null, all attributes are retrieved.
     * - attributesToHighlight: (array of strings) default list of attributes to highlight.
     *   If set to null, all indexed attributes are highlighted.
     * - attributesToSnippet**: (array of strings) default list of attributes to snippet alongside the number of words to return (syntax is attributeName:nbWords).
     *   By default no snippet is computed. If set to null, no snippet is computed.
     * - attributesToIndex: (array of strings) the list of fields you want to index.
     *   If set to null, all textual and numerical attributes of your objects are indexed, but you should update it to get optimal results.
     *   This parameter has two important uses:
     *     - Limit the attributes to index: For example if you store a binary image in base64, you want to store it and be able to
     *       retrieve it but you don't want to search in the base64 string.
     *     - Control part of the ranking*: (see the ranking parameter for full explanation) Matches in attributes at the beginning of
     *       the list will be considered more important than matches in attributes further down the list.
     *       In one attribute, matching text at the beginning of the attribute will be considered more important than text after, you can disable
     *       this behavior if you add your attribute inside `unordered(AttributeName)`, for example attributesToIndex: ["title", "unordered(text)"].
     * - attributesForFaceting: (array of strings) The list of fields you want to use for faceting.
     *   All strings in the attribute selected for faceting are extracted and added as a facet. If set to null, no attribute is used for faceting.
     * - attributeForDistinct: (string) The attribute name used for the Distinct feature. This feature is similar to the SQL "distinct" keyword: when enabled
     *   in query with the distinct=1 parameter, all hits containing a duplicate value for this attribute are removed from results.
     *   For example, if the chosen attribute is show_name and several hits have the same value for show_name, then only the best one is kept and others are removed.
     * - ranking: (array of strings) controls the way results are sorted.
     *   We have six available criteria:
     *    - typo: sort according to number of typos,
     *    - geo: sort according to decreassing distance when performing a geo-location based search,
     *    - proximity: sort according to the proximity of query words in hits,
     *    - attribute: sort according to the order of attributes defined by attributesToIndex,
     *    - exact:
     *        - if the user query contains one word: sort objects having an attribute that is exactly the query word before others.
     *          For example if you search for the "V" TV show, you want to find it with the "V" query and avoid to have all popular TV
     *          show starting by the v letter before it.
     *        - if the user query contains multiple words: sort according to the number of words that matched exactly (and not as a prefix).
     *    - custom: sort according to a user defined formula set in **customRanking** attribute.
     *   The standard order is ["typo", "geo", "proximity", "attribute", "exact", "custom"]
     * - customRanking: (array of strings) lets you specify part of the ranking.
     *   The syntax of this condition is an array of strings containing attributes prefixed by asc (ascending order) or desc (descending order) operator.
     *   For example `"customRanking" => ["desc(population)", "asc(name)"]`
     * - queryType: Select how the query words are interpreted, it can be one of the following value:
     *   - prefixAll: all query words are interpreted as prefixes,
     *   - prefixLast: only the last word is interpreted as a prefix (default behavior),
     *   - prefixNone: no query word is interpreted as a prefix. This option is not recommended.
     * - highlightPreTag: (string) Specify the string that is inserted before the highlighted parts in the query result (default to "<em>").
     * - highlightPostTag: (string) Specify the string that is inserted after the highlighted parts in the query result (default to "</em>").
     * - optionalWords: (array of strings) Specify a list of words that should be considered as optional when found in the query.
     */
    public function setSettings($settings) {
        return $this->client->request($this->context, "PUT", "/1/indexes/" . $this->urlIndexName . "/settings", array(), $settings);
    }

    /*
     * List all existing user keys associated to this index with their associated ACLs
     *
     */
    public function listUserKeys() {
        return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/keys");
    }

    /*
     * Get ACL of a user key associated to this index
     *
     */
    public function getUserKeyACL($key) {
        return $this->client->request($this->context, "GET", "/1/indexes/" . $this->urlIndexName . "/keys/" . $key);
    }

    /*
     * Delete an existing user key associated to this index
     *
     */
    public function deleteUserKey($key) {
        return $this->client->request($this->context, "DELETE", "/1/indexes/" . $this->urlIndexName . "/keys/" . $key);
    }

    /*
     * Create a new user key associated to this index
     *
     * @param acls the list of ACL for this key. Defined by an array of strings that
     * can contains the following values:
     *   - search: allow to search (https and http)
     *   - addObject: allows to add/update an object in the index (https only)
     *   - deleteObject : allows to delete an existing object (https only)
     *   - deleteIndex : allows to delete index content (https only)
     *   - settings : allows to get index settings (https only)
     *   - editSettings : allows to change index settings (https only)
     * @param validity the number of seconds after which the key will be automatically removed (0 means no time limit for this key)
     * @param maxQueriesPerIPPerHour Specify the maximum number of API calls allowed from an IP address per hour.  Defaults to 0 (no rate limit).
     * @param maxHitsPerQuery Specify the maximum number of hits this API key can retrieve in one call. Defaults to 0 (unlimited)
     */
    public function addUserKey($acls, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0) {
        return $this->client->request($this->context, "POST", "/1/indexes/" . $this->urlIndexName . "/keys", array(),
            array("acl" => $acls, "validity" => $validity, "maxQueriesPerIPPerHour" => $maxQueriesPerIPPerHour, "maxHitsPerQuery" => $maxHitsPerQuery));
    }

    /**
     * Send a batch request
     * @param  $requests an associative array defining the batch request body
     */
    public function batch($requests) {
        return $this->client->request($this->context, "POST", "/1/indexes/" . $this->urlIndexName . "/batch", array(), $requests);
    }

    /**
     * Build a batch request
     * @param  $action the batch action
     * @param  $objects the array of objects
     * @param  $withObjectID set an 'objectID' attribute
     * @param  $objectIDKey the objectIDKey
     */
    private function buildBatch($action, $objects, $withObjectID, $objectIDKey = "objectID") {
        $requests = array();
        foreach ($objects as $obj) {
            $req = array("action" => $action, "body" => $obj);
            if ($withObjectID && array_key_exists($objectIDKey, $obj)) {
                $req["objectID"] = (string) $obj[$objectIDKey];
            }
            array_push($requests, $req);
        }
        return array("requests" => $requests);
    }
}

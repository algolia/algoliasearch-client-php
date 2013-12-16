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
 */
namespace AlgoliaSearch;


class AlgoliaException extends \Exception { }

/** 
 * Entry point in the PHP API.
 * You should instantiate a Client object with your ApplicationID, ApiKey and Hosts 
 * to start using Algolia Search API
 */
class Client {
    /*
     * Algolia Search initialization
     * @param applicationID the application ID you have in your admin interface
     * @param apiKey a valid API key for the service
     * @param hostsArray the list of hosts that you have received for the service
     */
    public function __construct($applicationID, $apiKey, $hostsArray = null) {
        $this->applicationID = $applicationID;
        $this->apiKey = $apiKey;
        if ($hostsArray == null) {
            $this->hostsArray = array($applicationID . "-1.algolia.io", $applicationID . "-2.algolia.io", $applicationID . "-3.algolia.io");
        } else {
            $this->hostsArray = $hostsArray;
        }

        if(!function_exists('curl_init')){
            throw new \Exception('AlgoliaSearch requires the CURL PHP extension.');
        }

        if(!function_exists('json_decode')){
            throw new \Exception('AlgoliaSearch requires the JSON PHP extension.');
        }
        if ($this->applicationID == null || mb_strlen($this->applicationID) == 0) {
            throw new \Exception('AlgoliaSearch requires an applicationID.');            
        }
        if ($this->apiKey == null || mb_strlen($this->apiKey) == 0) {
            throw new \Exception('AlgoliaSearch requires an apiKey.');            
        }
        if ($this->hostsArray == null || count($this->hostsArray) == 0) {
            throw new \Exception('AlgoliaSearch requires a list of hostnames.'); 
        } else {
            // randomize elements of hostsArray (act as a kind of load-balancer)
            shuffle($this->hostsArray);
        }
        
        // initialize curl library
        $this->curlHandle = curl_init();
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, array(
                    'X-Algolia-Application-Id: ' . $this->applicationID,
                    'X-Algolia-API-Key: ' . $this->apiKey,
                    'Content-type: application/json'
                    ));
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "Algolia for PHP");
        //Return the output instead of printing it
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($this->curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curlHandle, CURLOPT_CAINFO, __DIR__ . '/resources/ca-bundle.crt');
    }

    /*
     * List all existing indexes
     * return an object in the form:
     * array("items" => array(
     *                        array("name" => "contacts", "createdAt" => "2013-01-18T15:33:13.556Z"),
     *                        array("name" => "notes", "createdAt" => "2013-01-18T15:33:13.556Z")
     *                        ))
     */
    public function listIndexes() {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/");
    }

    /*
     * Delete an index
     *
     * @param indexName the name of index to delete
     * return an object containing a "deletedAt" attribute
     */
    public function deleteIndex($indexName) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/indexes/" . urlencode($indexName));
    }
  
    /**
     * Move an existing index.
     * @param srcIndexName the name of index to copy.
     * @param dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overriten if it already exist).
     */
    public function moveIndex($srcIndexName, $dstIndexName) {
        $request = array("operation" => "move", "destination" => $dstIndexName);
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . urlencode($srcIndexName) . "/operation", array(), $request);
    }
    
    /**
     * Copy an existing index.
     * @param srcIndexName the name of index to copy.
     * @param dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overriten if it already exist).
     */
    public function copyIndex($srcIndexName, $dstIndexName) {
        $request = array("operation" => "copy", "destination" => $dstIndexName);
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . urlencode($srcIndexName) . "/operation", array(), $request);
    }
    
    /**
     * Return last logs entries.
     * @param offset Specify the first entry to retrieve (0-based, 0 is the most recent log entry).
     * @param length Specify the maximum number of entries to retrieve starting at offset. Maximum allowed value: 1000.
     */
    public function getLogs($offset = 0, $length = 10) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/logs?offset=" . $offset . "&length=" . $length);
    }
    
    /*
     * Get the index object initialized (no server call needed for initialization)
    
     * @param indexName the name of index
     */
    public function initIndex($indexName) {
        return new Index($this->curlHandle, $this->hostsArray, $indexName);
    }

    /*
     * List all existing user keys with their associated ACLs
     *
     */
    public function listUserKeys() {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/keys");
    }

    /*
     * Get ACL of a user key
     *
     */
    public function getUserKeyACL($key) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/keys/" . $key);
    }

    /*
     * Delete an existing user key
     *
     */
    public function deleteUserKey($key) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/keys/" . $key);
    }

    /*
     * Create a new user key
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/keys", array(), 
             array("acl" => $acls, "validity" => $validity, "maxQueriesPerIPPerHour" => $maxQueriesPerIPPerHour, "maxHitsPerQuery" => $maxHitsPerQuery));
    }

    protected $applicationID;
    protected $apiKey;
    protected $hostsArray;
    protected $curlHandle;
}

/*
 * Contains all the functions related to one index
 * You should use Client.initIndex(indexName) to retrieve this object
 */
class Index {
    /*
     * Index initialization (You should not call this initialized yourself)
     */
    public function __construct($curlHandle, $hostsArray, $indexName) {
        $this->curlHandle = $curlHandle;
        $this->hostsArray = $hostsArray;
        $this->indexName = $indexName;
        $this->urlIndexName = urlencode($indexName);
    }

    /*
     * Add an object in this index
     * 
     * @param content contains the object to add inside the index. 
     *  The object is represented by an associative array
     * @param objectID (optional) an objectID you want to attribute to this object 
     * (if the attribute already exist the old object will be overwrite)
     */
    public function addObject($content, $objectID = null) {

        if ($objectID === null) {
            return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName, array(), $content);
        } else {
            return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "PUT", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($objectID), array(), $content);
        }
    }

    /*
     * Add several objects
     * 
     * @param objects contains an array of objects to add. If the object contains an objectID
     */
    public function addObjects($objects, $objectIDKey = "objectID") {
        $requests = array();
        for ($i = 0; $i < count($objects); ++$i) {
            $obj = $objects[$i];
            if (array_key_exists($objectIDKey, $obj)) {
                array_push($requests, array("action" => "updateObject", "objectID" => $obj[$objectIDKey], "body" => $obj));
            } else {
                array_push($requests, array("action" => "addObject", "body" => $obj));
            }
        }
        $request = array("requests" => $requests);
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/batch", array(), $request);
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
            return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/" . $id);
        else
            return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/" . $id, array("attributes" => $attributesToRetrieve));
    }

    /*
     * Update partially an object (only update attributes passed in argument)
     * 
     * @param partialObject contains the object attributes to override, the 
     *  object must contains an objectID attribute
     */
    public function partialUpdateObject($partialObject) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($partialObject["objectID"] . "/partial"), array(), $partialObject);
    }

    /*
     * Partially Override the content of several objects
     * 
     * @param objects contains an array of objects to update (each object must contains a objectID attribute)
     */
    public function partialUpdateObjects($objects, $objectIDKey = "objectID") {
        $requests = array();
        for ($i = 0; $i < count($objects); ++$i) {
            $obj = $objects[$i];
            array_push($requests, array("action" => "partialUpdateObject", "objectID" => $obj[$objectIDKey], "body" => $obj));
        }
        $request = array("requests" => $requests);
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/batch", array(), $request);
    }

    /*
     * Override the content of object
     * 
     * @param object contains the object to save, the object must contains an objectID attribute
     */
    public function saveObject($object) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "PUT", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($object["objectID"]), array(), $object);
    }

    /*
     * Override the content of several objects
     * 
     * @param objects contains an array of objects to update (each object must contains a objectID attribute)
     */
    public function saveObjects($objects, $objectIDKey = "objectID") {
        $requests = array();
        for ($i = 0; $i < count($objects); ++$i) {
            $obj = $objects[$i];
            array_push($requests, array("action" => "updateObject", "objectID" => $obj[$objectIDKey], "body" => $obj));
        }
        $request = array("requests" => $requests);
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/batch", array(), $request);
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($objectID));
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
     */
    public function search($query, $args = null) {
        if ($args === null) {
            $args = array();
        }
        $args["query"] = $query;
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName, $args);
    }

    /*
     * Browse all index content
     *
     * @param page Pagination parameter used to select the page to retrieve.
     *             Page is zero-based and defaults to 0. Thus, to retrieve the 10th page you need to set page=9
     * @param hitsPerPage: Pagination parameter used to select the number of hits per page. Defaults to 1000.
     */
    public function browse($page = 0, $hitsPerPage = 1000) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/browse", 
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
            $res = AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/task/" . $taskID);
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/settings");
    }

    /*
     * This function deletes the index content. Settings and index specific API keys are kept untouched.
     */
    public function clearIndex() {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/clear");
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
     * - ranking: (array of strings) controls the way results are sorted.
     *   We have six available criteria: 
     *    - typo: sort according to number of typos,
     *    - geo: sort according to decreassing distance when performing a geo-location based search,
     *    - proximity: sort according to the proximity of query words in hits,
     *    - attribute: sort according to the order of attributes defined by attributesToIndex,
     *    - exact: sort according to the number of words that are matched identical to query word (and not as a prefix),
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "PUT", "/1/indexes/" . $this->urlIndexName . "/settings", array(), $settings);
    }

    /*
     * List all existing user keys associated to this index with their associated ACLs
     *
     */
    public function listUserKeys() {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/keys");
    }

    /*
     * Get ACL of a user key associated to this index
     *
     */
    public function getUserKeyACL($key) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName . "/keys/" . $key);
    }

    /*
     * Delete an existing user key associated to this index
     *
     */
    public function deleteUserKey($key) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/indexes/" . $this->urlIndexName . "/keys/" . $key);
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/keys", array(), 
            array("acl" => $acls, "validity" => $validity, "maxQueriesPerIPPerHour" => $maxQueriesPerIPPerHour, "maxHitsPerQuery" => $maxHitsPerQuery));
    }

    private $indexName;
    private $urlIndexName;
    private $hostsArray;
    private $curlHandle;
}

function AlgoliaUtils_request($curlHandle, $hostsArray, $method, $path, $params = array(), $data = array()) {
    $exception = null;
    foreach ($hostsArray as &$host) {
        try {
            $res = AlgoliaUtils_requestHost($curlHandle, $method, $host, $path, $params, $data);
            if ($res !== null)
                return $res;
        } catch (AlgoliaException $e) {
            throw $e; 
        } catch (Exception $e) {
            $exception = $e;
        }
    }
    if ($exception == null)
        throw new AlgoliaException('Hosts unreachable');
    else
        throw $exception;
}

function AlgoliaUtils_requestHost($curlHandle, $method, $host, $path, $params, $data) {
    $url = "https://" . $host . $path;

    if ($params != null && count($params) > 0) {
        $params2 = array();
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                $params2[$key] = json_encode($val);
            } else {
                $params2[$key] = $val;
            }
        }
        $url .= "?" . http_build_query($params2);
    }

    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

    if ($method === 'GET') {
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
        curl_setopt($curlHandle, CURLOPT_POST, false);
    } else if ($method === 'POST') {
        $body = ($data) ? json_encode($data) : '';
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
    } elseif ($method === 'DELETE') {
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } elseif ($method === 'PUT') {
        $body = ($data) ? json_encode($data) : '';
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($curlHandle);
    if ($response === false) {
        throw new \Exception(curl_error($curlHandle));
    }
    $http_status = (int)curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

    if ($http_status === 0 || $http_status === 503) {
        // Could not reach host or service unavailable, try with another one if we have it
        return null;
    }
    if ($http_status === 403) {
        throw new AlgoliaException("Invalid Application-ID or API-Key");
    }
    if ($http_status === 404) {
        throw new AlgoliaException("Resource does not exist");
    }
    $answer = json_decode($response, true);
    $errorMsg = isset($answer['message']) ? $answer['message'] : null;

    switch (json_last_error()) {
        case JSON_ERROR_DEPTH:
            $errorMsg = 'JSON parsing error: maximum stack depth exceeded';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $errorMsg = 'JSON parsing error: unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            $errorMsg = 'JSON parsing error: syntax error, malformed JSON';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $errorMsg = 'JSON parsing error: underflow or the modes mismatch';
            break;
        case JSON_ERROR_UTF8:
            $errorMsg = 'JSON parsing error: malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        case JSON_ERROR_NONE:
        default:
            break;
    }
    if ($errorMsg !== null)
        throw new AlgoliaException($errorMsg);

    // Check is there is an error which is not a 403/404/503
    if (intval(floor($http_status / 100)) !== 2) {
        throw new AlgoliaException($answer["message"]);
    }
    return $answer;
}

?>

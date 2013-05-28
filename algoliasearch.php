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
 * You should instanciate a Client object with your ApplicationID, ApiKey and Hosts 
 * to start using Algolia Search API
 */
class Client {
    /*
     * Algolia Search library initialization
     * @param applicationID the application ID you have in your admin interface
     * @param apiKey a valid API key for the service
     * @param hostsArray the list of hosts that you have received for the service
     */
    public function __construct($applicationID, $apiKey, $hostsArray) {
        $this->applicationID = $applicationID;
        $this->apiKey = $apiKey;
        $this->hostsArray = $hostsArray;

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
        //Return the output instead of printing it
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);
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
     * return an object whith in the form array("deletedAt" => "2013-01-18T15:33:13.556Z")
     */
    public function deleteIndex($indexName) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/indexes/" . $indexName);
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
     *   - addObject: allows to add a new object in the index (https only)
     *   - updateObject : allows to change content of an existing object (https only)
     *   - deleteObject : allows to delete an existing object (https only)
     *   - deleteIndex : allows to delete index content (https only)
     *   - settings : allows to get index settings (https only)
     *   - editSettings : allows to change index settings (https only)
     */
    public function addUserKey($acls) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/keys", array(), array("acl" => $acls));
    }

    private $applicationID;
    private $apiKey;
    private $hostsArray;
    private $curlHandle;
}

/*
 * Contains all the functions related to one index
 * You should use Client.initIndex(indexName) to retrieve this object
 */
class Index {
    /*
     * Index initialization (You should not call this initialized yourself)
     * @param applicationID the application ID you have in your admin interface
     * @param apiKey a valid API key for the service
     * @param hostsArray the list of hosts that you have received for the service
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
     * @param objects contains an array of objects to add
     */
    public function addObjects($objects) {
        $requests = array();
        for ($i = 0; $i < count($objects); ++$i) {
            array_push($requests, array("action" => "addObject", "body" => $object[$i]));
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
     * @param partialObject contains the javascript attributes to override, the 
     *  object must contains an objectID attribute
     */
    public function partialUpdateObject($partialObject) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "POST", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($partialObject["objectID"] . "/partial"), array(), $partialObject);
    }

    /*
     * Override the content of object
     * 
     * @param object contains the javascript object to save, the object must contains an objectID attribute
     */
    public function saveObject($object) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "PUT", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($object["objectID"]), array(), $object);
    }

    /*
     * Override the content of several objects
     * 
     * @param objects contains an array of objects to update (each object must contains a objectID attribute)
     */
    public function saveObjects($objects) {
        $requests = array();
        for ($i = 0; $i < count($objects); ++$i) {
            $obj = $object[$i];
            array_push($requests, array("action" => "updateObject", "objetID" => $obj["objectID"], "body" => $obj));
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
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "DELETE", "/1/indexes/" . $this->urlIndexName . "/" . urlencode($objectID));
    }

    /*
     * Search inside the index
     *
     * @param query the full text query
     * @param args (optional) if set, contains an associative array with query parameters:
     *  - attributes: a string that contains attribute names to retrieve separated by a comma. 
     *    By default all attributes are retrieved.
     *  - attributesToHighlight: a string that contains attribute names to highlight separated by a comma. 
     *    By default all attributes are highlighted.
     *  - minWordSizeForApprox1: the minimum number of characters in a query word to accept one typo in this word. 
     *    Defaults to 3.
     *  - minWordSizeForApprox2: the minimum number of characters in a query word to accept two typos in this word.
     *     Defaults to 7.
     *  - getRankingInfo: if set to 1, the result hits will contain ranking information in 
     *     _rankingInfo attribute
     *  - page: (pagination parameter) page to retrieve (zero base). Defaults to 0.
     *  - hitsPerPage: (pagination parameter) number of hits per page. Defaults to 10.
     *  - aroundLatLng let you search for entries around a given latitude/longitude (two float separated 
     *    by a ',' for example aroundLatLng=47.316669,5.016670). 
     *    You can specify the maximum distance in meters with aroundRadius parameter (in meters).
     *    At indexing, geoloc of an object should be set with _geoloc attribute containing lat and lng attributes (for example {"_geoloc":{"lat":48.853409, "lng":2.348800}})
     *  - insideBoundingBox let you search entries inside a given area defined by the two extreme points of 
     *    a rectangle (defined by 4 floats: p1Lat,p1Lng,p2Lat, p2Lng.
     *    For example insideBoundingBox=47.3165,4.9665,47.3424,5.0201).
     *    At indexing, geoloc of an object should be set with _geoloc attribute containing lat and lng attributes (for example {"_geoloc":{"lat":48.853409, "lng":2.348800}})
     *  - tags let you filter the query by a set of tags (contains a list of tags separated by ','). 
     *    At indexing, tags should be added in _tags attribute of objects (for example {"_tags":["tag1","tag2"]} )
     */
    public function search($query, $args = null) {
        if ($args === null) {
            $args = array();
        }
        $args["query"] = $query;
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "GET", "/1/indexes/" . $this->urlIndexName, $args);
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
            if ($res->hasError())
                return $res;
            $content = $res->getContent();
            if ($content["status"] === "published")
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
     * Set settings for this index
     * 
     * @param settigns the settings object that can contains :
     *  - minWordSizeForApprox1 (integer) the minimum number of characters to accept one typo (default = 3)
     *  - minWordSizeForApprox2: (integer) the minimum number of characters to accept two typos (default = 7)
     *  - hitsPerPage: (integer) the number of hits per page (default = 10)
     *  - attributesToRetrieve: (array of strings) default list of attributes to retrieve for objects
     *  - attributesToHighlight: (array of strings) default list of attributes to highlight
     *  - attributesToIndex: (array of strings) the list of fields you want to index. 
     *    By default all textual attributes of your objects are indexed, but you should update it to get optimal 
     *    results. This parameter has two important uses:
     *       - Limit the attributes to index. 
     *         For example if you store a binary image in base64, you want to store it in the index but you 
     *         don't want to use the base64 string for search.
     *       - Control part of the ranking (see the ranking parameter for full explanation). 
     *         Matches in attributes at the beginning of the list will be considered more important than matches 
     *         in attributes further down the list.
     *  - ranking: (array of strings) controls the way results are sorted. 
     *     We have four available criteria: 
     *       - typo (sort according to number of typos), 
     *       - geo: (sort according to decreassing distance when performing a geo-location based search),
     *       - position (sort according to the matching attribute), 
     *       - custom which is user defined
     *     (the standard order is ["typo", "geo", position", "custom"])
     *  - customRanking: (array of strings) lets you specify part of the ranking. 
     *    The syntax of this condition is an array of strings containing attributes prefixed 
     *    by asc (ascending order) or desc (descending order) operator.
     */
    public function setSettings($settings) {
        return AlgoliaUtils_request($this->curlHandle, $this->hostsArray, "PUT", "/1/indexes/" . $this->urlIndexName . "/settings", array(), $settings);
    }


    private $indexName;
    private $urlIndexName;
    private $hostsArray;
    private $curlHandle;
}

function AlgoliaUtils_request($curlHandle, $hostsArray, $method, $path, $params = array(), $data = array()) {
    foreach ($hostsArray as &$host) {
        try {
            $res = AlgoliaUtils_requestHost($curlHandle, $method, $host, $path, $params, $data);
            if ($res !== null)
                return $res;
        } catch (AlgoliaException $e) {
            throw e; 
        } catch (Exception $e) {
        }
    }
    throw new AlgoliaException('Hosts unreachable');
}

function AlgoliaUtils_requestHost($curlHandle, $method, $host, $path, $params, $data) {
    $url = "https://" . $host . $path;
    if ($params != null && count($params) > 0)
        $url .= "?" . http_build_query($params);
    curl_setopt($curlHandle, CURLOPT_URL, $url);
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
    $errorMsg = null;

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

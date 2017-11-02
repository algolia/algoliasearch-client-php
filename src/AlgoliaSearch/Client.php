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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

/**
 * Entry point in the PHP API.
 * You should instantiate a Client object with your ApplicationID, ApiKey and Hosts
 * to start using Algolia Search API.
 */
class Client
{
    const CAINFO = 'cainfo';
    const GUZZLE_OPTIONS = 'guzzle';
    const PLACES_ENABLED = 'placesEnabled';
    const FAILING_HOSTS_CACHE = 'failingHostsCache';

    /**
     * @var ClientContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $caInfoPath;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @var bool
     */
    protected $placesEnabled = false;

    /**
     * Algolia Search initialization.
     *
     * @param string     $applicationID the application ID you have in your admin interface
     * @param string     $apiKey        a valid API key for the service
     * @param array|null $hostsArray    the list of hosts that you have received for the service
     * @param array      $options
     *
     * @throws \Exception
     */
    public function __construct($applicationID, $apiKey, $hostsArray = null, $options = array())
    {
        if (!function_exists('json_decode')) {
            throw new \Exception('AlgoliaSearch requires the JSON PHP extension.');
        }

        $this->caInfoPath = __DIR__.'/../../resources/ca-bundle.crt';
        $guzzleRequestOptions = [];
        foreach ($options as $option => $value) {
            switch ($option) {
                case self::CAINFO:
                    $this->caInfoPath = $value;
                    break;
                case self::GUZZLE_OPTIONS:
                    $guzzleRequestOptions = $this->checkGuzzleRequestOptions($value);
                    break;
                case self::PLACES_ENABLED:
                    $this->placesEnabled = (bool) $value;
                    break;
                case self::FAILING_HOSTS_CACHE:
                    if (! $value instanceof FailingHostsCache) {
                        throw new \InvalidArgumentException('failingHostsCache must be an instance of \AlgoliaSearch\FailingHostsCache.');
                    }
                    break;
                default:
                    throw new \Exception('Unknown option: '.$option);
            }
        }

        $failingHostsCache = isset($options[self::FAILING_HOSTS_CACHE]) ? $options[self::FAILING_HOSTS_CACHE] : null;
        $this->context = new ClientContext($applicationID, $apiKey, $hostsArray, $this->placesEnabled, $failingHostsCache);
        $this->guzzleClient = new GuzzleClient($guzzleRequestOptions);
    }

    /**
     * Change the default connect timeout of 1s to a custom value
     * (only useful if your server has a very slow connectivity to Algolia backend).
     *
     * @param int $connectTimeout the connection timeout
     * @param int $timeout        the read timeout for the query
     * @param int $searchTimeout  the read timeout used for search queries only
     *
     * @throws AlgoliaException
     */
    public function setConnectTimeout($connectTimeout, $timeout = 30, $searchTimeout = 5)
    {
        $isPhpOld = version_compare(phpversion(), '5.2.3', '<');

        if ($isPhpOld && $this->context->connectTimeout < 1) {
            throw new AlgoliaException(
                "The timeout can't be a float with a PHP version less than 5.2.3"
            );
        }
        $this->context->connectTimeout = $connectTimeout;
        $this->context->readTimeout = $timeout;
        $this->context->searchTimeout = $searchTimeout;
    }

    /**
     * Allow to use IP rate limit when you have a proxy between end-user and Algolia.
     * This option will set the X-Forwarded-For HTTP header with the client IP
     * and the X-Forwarded-API-Key with the API Key having rate limits.
     *
     * @param string $adminAPIKey     the admin API Key you can find in your dashboard
     * @param string $endUserIP       the end user IP (you can use both IPV4 or IPV6 syntax)
     * @param string $rateLimitAPIKey the API key on which you have a rate limit
     */
    public function enableRateLimitForward($adminAPIKey, $endUserIP, $rateLimitAPIKey)
    {
        $this->context->setRateLimit($adminAPIKey, $endUserIP, $rateLimitAPIKey);
    }

    /**
     * The aggregation of the queries to retrieve the latest query
     * uses the IP or the user token to work efficiently.
     * If the queries are made from your backend server,
     * the IP will be the same for all of the queries.
     * We're supporting the following HTTP header to forward the IP of your end-user
     * to the engine, you just need to set it for each query.
     *
     * @see https://www.algolia.com/doc/faq/analytics/will-the-analytics-still-work-if-i-perform-the-search-through-my-backend
     *
     * @param string $ip
     */
    public function setForwardedFor($ip)
    {
        $this->context->setForwardedFor($ip);
    }

    /**
     * It's possible to use the following token to track users that have the same IP
     * or to track users that use different devices.
     *
     * @see https://www.algolia.com/doc/faq/analytics/will-the-analytics-still-work-if-i-perform-the-search-through-my-backend
     *
     * @param string $token
     */
    public function setAlgoliaUserToken($token)
    {
        $this->context->setAlgoliaUserToken($token);
    }

    /**
     * Disable IP rate limit enabled with enableRateLimitForward() function.
     */
    public function disableRateLimitForward()
    {
        $this->context->disableRateLimit();
    }

    /**
     * Call isAlive.
     */
    public function isAlive()
    {
        $this->request(
            $this->context,
            'GET',
            '/1/isalive',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Allow to set custom headers.
     *
     * @param string $key
     * @param string $value
     */
    public function setExtraHeader($key, $value)
    {
        $this->context->setExtraHeader($key, $value);
    }

    /**
     * This method allows to query multiple indexes with one API call.
     *
     * @param array  $queries
     * @param string $indexNameKey
     * @param string $strategy
     * @param array $requestHeaders
     *
     * @return mixed
     *
     * @throws AlgoliaException
     * @throws \Exception
     */
    public function multipleQueries($queries, $indexNameKey = 'indexName', $strategy = 'none', $requestHeaders = array())
    {
        if ($queries == null) {
            throw new \Exception('No query provided');
        }
        $requests = array();
        foreach ($queries as $query) {
            if (array_key_exists($indexNameKey, $query)) {
                $indexes = $query[$indexNameKey];
                unset($query[$indexNameKey]);
            } else {
                throw new \Exception('indexName is mandatory');
            }
            $req = array('indexName' => $indexes, 'params' => $this->buildQuery($query));

            array_push($requests, $req);
        }

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/*/queries',
            array(),
            array('requests' => $requests, 'strategy' => $strategy),
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->searchTimeout,
            $requestHeaders
        );
    }

    /**
     * List all existing indexes
     * return an object in the form:
     * array(
     *     "items" => array(
     *         array("name" => "contacts", "createdAt" => "2013-01-18T15:33:13.556Z"),
     *         array("name" => "notes", "createdAt" => "2013-01-18T15:33:13.556Z")
     *     )
     * ).
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function listIndexes($requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/indexes/',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Delete an index.
     *
     * @param string $indexName the name of index to delete
     *
     * @return mixed an object containing a "deletedAt" attribute
     */
    public function deleteIndex($indexName, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'DELETE',
            '/1/indexes/'.urlencode($indexName),
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Move an existing index.
     *
     * @param string $srcIndexName the name of index to copy.
     * @param string $dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overwritten
     *                             if it already exist).
     *
     * @return mixed
     */
    public function moveIndex($srcIndexName, $dstIndexName, $requestHeaders = array())
    {
        $request = array('operation' => 'move', 'destination' => $dstIndexName);

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/'.urlencode($srcIndexName).'/operation',
            array(),
            $request,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Copy an existing index.
     *
     * @param string $srcIndexName the name of index to copy.
     * @param string $dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overwritten
     *                             if it already exist).
     *
     * @return mixed
     */
    public function copyIndex($srcIndexName, $dstIndexName, $requestHeaders = array())
    {
        $request = array('operation' => 'copy', 'destination' => $dstIndexName);

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/'.urlencode($srcIndexName).'/operation',
            array(),
            $request,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Return last logs entries.
     *
     * @param int   $offset Specify the first entry to retrieve (0-based, 0 is the most recent log entry).
     * @param int   $length Specify the maximum number of entries to retrieve starting at offset. Maximum allowed value: 1000.
     * @param mixed $type
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function getLogs($offset = 0, $length = 10, $type = 'all', $requestHeaders = array())
    {
        if (gettype($type) == 'boolean') { //Old prototype onlyError
            if ($type) {
                $type = 'error';
            } else {
                $type = 'all';
            }
        }

        return $this->request(
            $this->context,
            'GET',
            '/1/logs?offset='.$offset.'&length='.$length.'&type='.$type,
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Add a userID to the mapping
     * @return an object containing a "updatedAt" attribute
     *
     * @throws AlgoliaException
     */
    public function assignUserID($userID, $clusterName, $requestHeaders = array())
    {
        $requestHeaders["X-Algolia-User-ID"] = $userID;

        $request = array('cluster' => $clusterName);

        return $this->request(
            $this->context,
            'POST',
            '/1/clusters/mapping',
            null,
            $request,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Remove a userID from the mapping
     * @return an object containing a "deletedAt" attribute
     *
     * @throws AlgoliaException
     */
    public function removeUserID($userID, $requestHeaders = array())
    {
        $requestHeaders["X-Algolia-User-ID"] = $userID;

        return $this->request(
            $this->context,
            'DELETE',
            '/1/clusters/mapping',
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * List available cluster in the mapping
     * return an object in the form:
     * array(
     *     "clusters" => array(
     *         array("clusterName" => "name", "nbRecords" => 0, "nbUserIDs" => 0, "dataSize" => 0)
     *     )
     * ).
     *
     * @return mixed
     * @throws AlgoliaException
     */
    public function listClusters($requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/clusters',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Get one userID in the mapping
     * return an object in the form:
     * array(
     *     "userID" => "userName",
     *     "clusterName" => "name",
     *     "nbRecords" => 0,
     *     "dataSize" => 0
     * ).
     *
     * @return mixed
     * @throws AlgoliaException
     */
    public function getUserID($userID, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/clusters/mapping/'.urlencode($userID),
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * List userIDs in the mapping
     * return an object in the form:
     * array(
     *     "userIDs" => array(
     *         array("userID" => "userName", "clusterName" => "name", "nbRecords" => 0, "dataSize" => 0)
     *     ),
     *     "page" => 0,
     *     "hitsPerPage" => 20
     * ).
     *
     * @return mixed
     * @throws AlgoliaException
     */
    public function listUserIDs($page = 0, $hitsPerPage = 20, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/clusters/mapping?page='.$page.'&hitsPerPage='.$hitsPerPage,
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Get top userID in the mapping
     * return an object in the form:
     * array(
     *     "topUsers" => array(
     *         "clusterName" => array(
     *             array("userID" => "userName", "nbRecords" => 0, "dataSize" => 0)
     *         )
     *     )
     * ).
     *
     * @return mixed
     * @throws AlgoliaException
     */
    public function getTopUserID($requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/clusters/mapping/top',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Search userIDs in the mapping
     * return an object in the form:
     * array(
     *     "hits" => array(
     *         array("userID" => "userName", "clusterName" => "name", "nbRecords" => 0, "dataSize" => 0)
     *     ),
     *     "nbHits" => 0
     *     "page" => 0,
     *     "hitsPerPage" => 20
     * ).
     *
     * @return mixed
     * @throws AlgoliaException
     */
    public function searchUserIDs($query, $clusterName = null, $page = null, $hitsPerPage = null, $requestHeaders = array())
    {
        $params = array();

        if ($query !== null) {
            $params['query'] = $query;
        }

        if ($clusterName !== null) {
            $params['cluster'] = $clusterName;
        }

        if ($page !== null) {
            $params['page'] = $page;
        }

        if ($hitsPerPage !== null) {
            $params['hitsPerPage'] = $hitsPerPage;
        }

        return $this->request(
            $this->context,
            'POST',
            '/1/clusters/mapping/search',
            null,
            $params,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Get the index object initialized (no server call needed for initialization).
     *
     * @param string $indexName the name of index
     *
     * @return Index
     *
     * @throws AlgoliaException
     */
    public function initIndex($indexName)
    {
        if (empty($indexName)) {
            throw new AlgoliaException('Invalid index name: empty string');
        }

        return new Index($this->context, $this, $indexName);
    }

    /**
     * List all existing API keys with their associated ACLs.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function listApiKeys($requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/keys',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * @return mixed
     * @deprecated use listApiKeys instead
     */
    public function listUserKeys()
    {
        return $this->listApiKeys();
    }

    /**
     * Get ACL of a API key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getApiKey($key, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/keys/'.$key,
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * @param $key
     * @return mixed
     * @deprecated use getApiKey instead
     */
    public function getUserKeyACL($key)
    {
        return $this->getApiKey($key);
    }

    /**
     * Delete an existing API key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function deleteApiKey($key, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'DELETE',
            '/1/keys/'.$key,
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
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
     * Create a new API key.
     *
     * @param array      $obj                    can be two different parameters:
     *                                           The list of parameters for this key. Defined by an array that
     *                                           can contain the following values:
     *                                           - acl: array of string
     *                                           - indices: array of string
     *                                           - validity: int
     *                                           - referers: array of string
     *                                           - description: string
     *                                           - maxHitsPerQuery: integer
     *                                           - queryParameters: string
     *                                           - maxQueriesPerIPPerHour: integer
     *                                           Or the list of ACL for this key. Defined by an array of string that
     *                                           can contains the following values:
     *                                           - search: allow to search (https and http)
     *                                           - addObject: allows to add/update an object in the index (https only)
     *                                           - deleteObject : allows to delete an existing object (https only)
     *                                           - deleteIndex : allows to delete index content (https only)
     *                                           - settings : allows to get index settings (https only)
     *                                           - editSettings : allows to change index settings (https only)
     * @param int        $validity               the number of seconds after which the key will be automatically removed (0 means
     *                                           no time limit for this key)
     * @param int        $maxQueriesPerIPPerHour Specify the maximum number of API calls allowed from an IP address per hour.
     *                                           Defaults to 0 (no rate limit).
     * @param int        $maxHitsPerQuery        Specify the maximum number of hits this API key can retrieve in one call.
     *                                           Defaults to 0 (unlimited)
     * @param array|null $indexes                Specify the list of indices to target (null means all)
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function addApiKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null, $requestHeaders = array())
    {
        if ($obj !== array_values($obj)) { // is dict of value
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

        if ($indexes != null) {
            $params['indexes'] = $indexes;
        }

        return $this->request(
            $this->context,
            'POST',
            '/1/keys',
            array(),
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * @param $obj
     * @param int $validity
     * @param int $maxQueriesPerIPPerHour
     * @param int $maxHitsPerQuery
     * @param null $indexes
     * @return mixed
     * @deprecated use addApiKey instead
     */
    public function addUserKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null)
    {
        return $this->addApiKey($obj, $validity, $maxQueriesPerIPPerHour, $maxHitsPerQuery, $indexes);
    }

    /**
     * Update an API key.
     *
     * @param string     $key
     * @param array      $obj                    can be two different parameters:
     *                                           The list of parameters for this key. Defined by a array that
     *                                           can contains the following values:
     *                                           - acl: array of string
     *                                           - indices: array of string
     *                                           - validity: int
     *                                           - referers: array of string
     *                                           - description: string
     *                                           - maxHitsPerQuery: integer
     *                                           - queryParameters: string
     *                                           - maxQueriesPerIPPerHour: integer
     *                                           Or the list of ACL for this key. Defined by an array of string that
     *                                           can contains the following values:
     *                                           - search: allow to search (https and http)
     *                                           - addObject: allows to add/update an object in the index (https only)
     *                                           - deleteObject : allows to delete an existing object (https only)
     *                                           - deleteIndex : allows to delete index content (https only)
     *                                           - settings : allows to get index settings (https only)
     *                                           - editSettings : allows to change index settings (https only)
     * @param int        $validity               the number of seconds after which the key will be automatically removed (0 means
     *                                           no time limit for this key)
     * @param int        $maxQueriesPerIPPerHour Specify the maximum number of API calls allowed from an IP address per hour.
     *                                           Defaults to 0 (no rate limit).
     * @param int        $maxHitsPerQuery        Specify the maximum number of hits this API key can retrieve in one call. Defaults
     *                                           to 0 (unlimited)
     * @param array|null $indexes                Specify the list of indices to target (null means all)
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function updateApiKey(
        $key,
        $obj,
        $validity = 0,
        $maxQueriesPerIPPerHour = 0,
        $maxHitsPerQuery = 0,
        $indexes = null,
        $requestHeaders = array()
    ) {
        if ($obj !== array_values($obj)) { // is dict of value
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
        if ($indexes != null) {
            $params['indexes'] = $indexes;
        }

        return $this->request(
            $this->context,
            'PUT',
            '/1/keys/'.$key,
            array(),
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * @param $key
     * @param $obj
     * @param int $validity
     * @param int $maxQueriesPerIPPerHour
     * @param int $maxHitsPerQuery
     * @param null $indexes
     * @return mixed
     * @deprecated use updateApiKey instead
     */
    public function updateUserKey(
        $key,
        $obj,
        $validity = 0,
        $maxQueriesPerIPPerHour = 0,
        $maxHitsPerQuery = 0,
        $indexes = null,
        $requestHeaders = array()
    ) {
        return $this->updateApiKey($key, $obj, $validity, $maxQueriesPerIPPerHour, $maxHitsPerQuery, $indexes, $requestHeaders);
    }

    /**
     * Send a batch request targeting multiple indices.
     *
     * @param array $requests an associative array defining the batch request body
     * @param array $requestHeaders
     *
     * @return mixed
     */
    public function batch($requests, $requestHeaders = array())
    {
        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/*/batch',
            array(),
            array('requests' => $requests),
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout,
            $requestHeaders
        );
    }

    /**
     * Generate a secured and public API Key from a list of query parameters and an
     * optional user token identifying the current user.
     *
     * @param string      $privateApiKey your private API Key
     * @param mixed       $query         the list of query parameters applied to the query (used as security)
     * @param string|null $userToken     an optional token identifying the current user
     *
     * @return string
     */
    public static function generateSecuredApiKey($privateApiKey, $query, $userToken = null)
    {
        if (is_array($query)) {
            $queryParameters = array();
            if (array_keys($query) !== array_keys(array_keys($query))) {
                // array of query parameters
                $queryParameters = $query;
            } else {
                // array of tags
                $tmp = array();
                foreach ($query as $tag) {
                    if (is_array($tag)) {
                        array_push($tmp, '('.implode(',', $tag).')');
                    } else {
                        array_push($tmp, $tag);
                    }
                }
                $tagFilters = implode(',', $tmp);
                $queryParameters['tagFilters'] = $tagFilters;
            }
            if ($userToken != null && strlen($userToken) > 0) {
                $queryParameters['userToken'] = $userToken;
            }
            $urlEncodedQuery = static::buildQuery($queryParameters);
        } else {
            if (strpos($query, '=') === false) {
                // String of tags
                $queryParameters = array('tagFilters' => $query);

                if ($userToken != null && strlen($userToken) > 0) {
                    $queryParameters['userToken'] = $userToken;
                }
                $urlEncodedQuery = static::buildQuery($queryParameters);
            } else {
                // url encoded query
                $urlEncodedQuery = $query;
                if ($userToken != null && strlen($userToken) > 0) {
                    $urlEncodedQuery = $urlEncodedQuery.'&userToken='.urlencode($userToken);
                }
            }
        }
        $content = hash_hmac('sha256', $urlEncodedQuery, $privateApiKey).$urlEncodedQuery;

        return base64_encode($content);
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public static function buildQuery($args)
    {
        foreach ($args as $key => $value) {
            if (gettype($value) == 'array') {
                $args[$key] = Json::encode($value);
            }
        }

        return http_build_query($args);
    }

    /**
     * @param ClientContext $context
     * @param string        $method
     * @param string        $path
     * @param array         $params
     * @param array         $data
     * @param array         $hostsArray
     * @param int           $connectTimeout
     * @param int           $readTimeout
     * @param array         $requestHeaders
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function request(
        $context,
        $method,
        $path,
        $params,
        $data,
        $hostsArray,
        $connectTimeout,
        $readTimeout,
        $requestHeaders = array()
    ) {
        $exceptions = array();
        $cnt = 0;
        foreach ($hostsArray as &$host) {
            $cnt += 1;
            if ($cnt == 3) {
                $connectTimeout += 2;
                $readTimeout += 10;
            }
            try {
                $res = $this->doRequest($context, $method, $host, $path, $params, $data, $connectTimeout, $readTimeout, $requestHeaders);
                if ($res !== null) {
                    return $res;
                }
            } catch (AlgoliaException $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[$host] = $e->getMessage();
                if ($context instanceof ClientContext) {
                    $context->addFailingHost($host); // Needs to be before the rotation otherwise it will not be rotated
                    $context->rotateHosts();
                }
            }
        }
        throw new AlgoliaConnectionException('Hosts unreachable: '.implode(',', $exceptions));
    }

    /**
     * @param ClientContext $context
     * @param string        $method
     * @param string        $host
     * @param string        $path
     * @param array         $params
     * @param array         $data
     * @param int           $connectTimeout
     * @param int           $readTimeout
     * @param array         $requestHeaders
     *
     * @return mixed
     *
     * @throws AlgoliaException
     * @throws \Exception
     */
    public function doRequest(
        $context,
        $method,
        $host,
        $path,
        $params,
        $data,
        $connectTimeout,
        $readTimeout,
        $requestHeaders = array()
    ) {
        if (strpos($host, 'http') === 0) {
            $url = $host.$path;
        } else {
            $url = 'https://'.$host.$path;
        }

        if ($params != null && count($params) > 0) {
            $params2 = array();
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    $params2[$key] = Json::encode($val);
                } else {
                    $params2[$key] = $val;
                }
            }
            $url .= '?'.http_build_query($params2);
        }

        $options = [];

        $defaultHeaders = null;
        if ($context->adminAPIKey == null) {
            $defaultHeaders = array(
                'X-Algolia-Application-Id' => $context->applicationID,
                'X-Algolia-API-Key'        => $context->apiKey,
                'Content-type'             => 'application/json',
            );
        } else {
            $defaultHeaders = array(
                'X-Algolia-Application-Id' => $context->applicationID,
                'X-Algolia-API-Key'        => $context->adminAPIKey,
                'X-Forwarded-For'          => $context->endUserIP,
                'X-Algolia-UserToken'      => $context->algoliaUserToken,
                'X-Forwarded-API-Key'      => $context->rateLimitAPIKey,
                'Content-type'             => 'application/json',
            );
        }

        $headers = array_merge($defaultHeaders, $context->headers, $requestHeaders);
        $headers['User-Agent'] = Version::getUserAgent();

        $options[GuzzleRequestOptions::HEADERS] = $headers;
        $options[GuzzleRequestOptions::HTTP_ERRORS] = false;
        $options[GuzzleRequestOptions::VERIFY] = self::isGoogleAppEngine() ? false : $this->caInfoPath;
        $options[GuzzleRequestOptions::TIMEOUT] = $options[GuzzleRequestOptions::CONNECT_TIMEOUT] = $connectTimeout;
        $options[GuzzleRequestOptions::READ_TIMEOUT] = $readTimeout;

        if (in_array($method, ['POST', 'PUT'])) {
            $body = ($data) ? Json::encode($data) : '';
            $options[GuzzleRequestOptions::BODY] = $body;
        }

        $guzzleResponse = $this->guzzleClient->request($method, $url, $options);

        $http_status = $guzzleResponse->getStatusCode();
        $response = (string)$guzzleResponse->getBody();

        if ($http_status === 0 || $http_status === 503) {
            return;
        }

        $answer = Json::decode($response, true);

        if (intval($http_status / 100) == 4) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : $http_status.' error', $http_status);
        } elseif (intval($http_status / 100) != 2) {
            throw new \Exception($http_status.': '.$response, $http_status);
        }

        return $answer;
    }

    /**
     * Checks if Guzzle request options passed are valid Guzzle request options.
     *
     * @param array $options must be array but no type required while first test throw clear Exception
     *
     * @return array
     */
    protected function checkGuzzleRequestOptions($options)
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'AlgoliaSearch requires %s option to be array of valid Guzzle request options.',
                    static::GUZZLE_OPTIONS
                )
            );
        }

        $checkedOptions = array_intersect(array_keys($options), array_values($this->getGuzzleRequestOptions()));

        if (count($checkedOptions) !== count($options)) {
            $this->invalidOptions($options);
        }

        return $options;
    }

    /**
     * Get all available Guzzle request options.
     *
     * @return array
     */
    protected function getGuzzleRequestOptions()
    {
        return (new \ReflectionClass(GuzzleRequestOptions::class))->getConstants();
    }

    /**
     * throw clear Exception when bad Guzzle request option is set.
     *
     * @param array $options
     * @param string $errorMsg    add specific message for disambiguation
     */
    protected function invalidOptions(array $options = array(), $errorMsg = '')
    {
        throw new \OutOfBoundsException(
            sprintf(
                'AlgoliaSearch %s options keys are invalid. %s given. error message : %s',
                static::GUZZLE_OPTIONS,
                Json::encode($options),
                $errorMsg
            )
        );
    }

    /**
     * @return PlacesIndex
     */
    private function getPlacesIndex()
    {
        return new PlacesIndex($this->context, $this);
    }

    /**
     * @param string|null $appId
     * @param string|null $apiKey
     * @param array|null  $hostsArray
     * @param array       $options
     *
     * @return PlacesIndex
     */
    public static function initPlaces($appId = null, $apiKey = null, $hostsArray = null, $options = array())
    {
        $options['placesEnabled'] = true;
        $client = new static($appId, $apiKey, $hostsArray, $options);

        return $client->getPlacesIndex();
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Recommended way to check if script is running in Google App Engine:
     * https://github.com/google/google-api-php-client/blob/master/src/Google/Client.php#L799
     *
     * @return bool Returns true if running in Google App Engine
     */
    private static function isGoogleAppEngine()
    {
        return (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') !== false);
    }
}

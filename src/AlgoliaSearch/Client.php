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

/**
 * Entry point in the PHP API.
 * You should instantiate a Client object with your ApplicationID, ApiKey and Hosts
 * to start using Algolia Search API.
 */
class Client
{
    const CAINFO = 'cainfo';
    const CURLOPT = 'curloptions';
    const PLACES_ENABLED = 'placesEnabled';

    /**
     * @var ClientContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $caInfoPath;

    /**
     * @var array
     */
    protected $curlConstants;

    /**
     * @var array
     */
    protected $curlOptions = [];

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
    public function __construct($applicationID, $apiKey, $hostsArray = null, $options = [])
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('AlgoliaSearch requires the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new \Exception('AlgoliaSearch requires the JSON PHP extension.');
        }

        $this->caInfoPath = __DIR__.'/../../resources/ca-bundle.crt';
        foreach ($options as $option => $value) {
            switch ($option) {
                case self::CAINFO:
                    $this->caInfoPath = $value;
                    break;
                case self::CURLOPT:
                    $this->curlOptions = $this->checkCurlOptions($value);
                    break;
                case self::PLACES_ENABLED:
                    $this->placesEnabled = (bool) $value;
                    break;
                default:
                    throw new \Exception('Unknown option: '.$option);
            }
        }

        $this->context = new ClientContext($applicationID, $apiKey, $hostsArray, $this->placesEnabled);
    }

    /**
     * Release curl handle.
     */
    public function __destruct()
    {
    }

    /**
     * Change the default connect timeout of 2s to a custom value
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
        $version = curl_version();
        $isPhpOld = version_compare(phpversion(), '5.2.3', '<');
        $isCurlOld = version_compare($version['version'], '7.16.2', '<');

        if (($isPhpOld || $isCurlOld) && $this->context->connectTimeout < 1) {
            throw new AlgoliaException(
                "The timeout can't be a float with a PHP version less than 5.2.3 or a curl version less than 7.16.2"
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
     *
     * @return mixed
     *
     * @throws AlgoliaException
     * @throws \Exception
     */
    public function multipleQueries($queries, $indexNameKey = 'indexName', $strategy = 'none')
    {
        if ($queries == null) {
            throw new \Exception('No query provided');
        }
        $requests = [];
        foreach ($queries as $query) {
            if (array_key_exists($indexNameKey, $query)) {
                $indexes = $query[$indexNameKey];
                unset($query[$indexNameKey]);
            } else {
                throw new \Exception('indexName is mandatory');
            }
            $req = ['indexName' => $indexes, 'params' => $this->buildQuery($query)];

            array_push($requests, $req);
        }

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/*/queries?strategy='.$strategy,
            [],
            ['requests' => $requests],
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->searchTimeout
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
    public function listIndexes()
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/indexes/',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Delete an index.
     *
     * @param string $indexName the name of index to delete
     *
     * @return mixed an object containing a "deletedAt" attribute
     */
    public function deleteIndex($indexName)
    {
        return $this->request(
            $this->context,
            'DELETE',
            '/1/indexes/'.urlencode($indexName),
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
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
    public function moveIndex($srcIndexName, $dstIndexName)
    {
        $request = ['operation' => 'move', 'destination' => $dstIndexName];

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/'.urlencode($srcIndexName).'/operation',
            [],
            $request,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
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
    public function copyIndex($srcIndexName, $dstIndexName)
    {
        $request = ['operation' => 'copy', 'destination' => $dstIndexName];

        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/'.urlencode($srcIndexName).'/operation',
            [],
            $request,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
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
    public function getLogs($offset = 0, $length = 10, $type = 'all')
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
            $this->context->readTimeout
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
     * List all existing user keys with their associated ACLs.
     *
     * @return mixed
     *
     * @throws AlgoliaException
     */
    public function listUserKeys()
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/keys',
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Get ACL of a user key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getUserKeyACL($key)
    {
        return $this->request(
            $this->context,
            'GET',
            '/1/keys/'.$key,
            null,
            null,
            $this->context->readHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Delete an existing user key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function deleteUserKey($key)
    {
        return $this->request(
            $this->context,
            'DELETE',
            '/1/keys/'.$key,
            null,
            null,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Create a new user key.
     *
     * @param            $obj                    can be two different parameters:
     *                                           The list of parameters for this key. Defined by an array that
     *                                           can contain the following values:
     *                                           - acl: array of string
     *                                           - indices: array of string
     *                                           - validity: int
     *                                           - referrers: array of string
     *                                           - description: string
     *                                           - maxHitsPerQuery: integer
     *                                           - queryParameters: string
     *                                           - maxQueriesPerIPPerHour: integer
     *                                           Or the list of ACL for this key. Defined by an array of NSString that
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
    public function addUserKey($obj, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null)
    {
        if ($obj !== array_values($obj)) { // is dict of value
            $params = $obj;
            $params['validity'] = $validity;
            $params['maxQueriesPerIPPerHour'] = $maxQueriesPerIPPerHour;
            $params['maxHitsPerQuery'] = $maxHitsPerQuery;
        } else {
            $params = [
                'acl'                    => $obj,
                'validity'               => $validity,
                'maxQueriesPerIPPerHour' => $maxQueriesPerIPPerHour,
                'maxHitsPerQuery'        => $maxHitsPerQuery,
            ];
        }

        if ($indexes != null) {
            $params['indexes'] = $indexes;
        }

        return $this->request(
            $this->context,
            'POST',
            '/1/keys',
            [],
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Update a user key.
     *
     * @param string     $key
     * @param mixed      $obj                    can be two different parameters:
     *                                           The list of parameters for this key. Defined by a array that
     *                                           can contains the following values:
     *                                           - acl: array of string
     *                                           - indices: array of string
     *                                           - validity: int
     *                                           - referrers: array of string
     *                                           - description: string
     *                                           - maxHitsPerQuery: integer
     *                                           - queryParameters: string
     *                                           - maxQueriesPerIPPerHour: integer
     *                                           Or the list of ACL for this key. Defined by an array of NSString that
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
    public function updateUserKey(
        $key,
        $obj,
        $validity = 0,
        $maxQueriesPerIPPerHour = 0,
        $maxHitsPerQuery = 0,
        $indexes = null
    ) {
        if ($obj !== array_values($obj)) { // is dict of value
            $params = $obj;
            $params['validity'] = $validity;
            $params['maxQueriesPerIPPerHour'] = $maxQueriesPerIPPerHour;
            $params['maxHitsPerQuery'] = $maxHitsPerQuery;
        } else {
            $params = [
                'acl'                    => $obj,
                'validity'               => $validity,
                'maxQueriesPerIPPerHour' => $maxQueriesPerIPPerHour,
                'maxHitsPerQuery'        => $maxHitsPerQuery,
            ];
        }
        if ($indexes != null) {
            $params['indexes'] = $indexes;
        }

        return $this->request(
            $this->context,
            'PUT',
            '/1/keys/'.$key,
            [],
            $params,
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
        );
    }

    /**
     * Send a batch request targeting multiple indices.
     *
     * @param array $requests an associative array defining the batch request body
     *
     * @return mixed
     */
    public function batch($requests)
    {
        return $this->request(
            $this->context,
            'POST',
            '/1/indexes/*/batch',
            [],
            ['requests' => $requests],
            $this->context->writeHostsArray,
            $this->context->connectTimeout,
            $this->context->readTimeout
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
        $urlEncodedQuery = '';
        if (is_array($query)) {
            $queryParameters = [];
            if (array_keys($query) !== array_keys(array_keys($query))) {
                // array of query parameters
                $queryParameters = $query;
            } else {
                // array of tags
                $tmp = [];
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
                $queryParameters = ['tagFilters' => $query];

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
                $args[$key] = json_encode($value);
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
        $readTimeout
    ) {
        $exceptions = [];
        $cnt = 0;
        foreach ($hostsArray as &$host) {
            $cnt += 1;
            if ($cnt == 3) {
                $connectTimeout += 2;
                $readTimeout += 10;
            }
            try {
                $res = $this->doRequest($context, $method, $host, $path, $params, $data, $connectTimeout, $readTimeout);
                if ($res !== null) {
                    return $res;
                }
            } catch (AlgoliaException $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[$host] = $e->getMessage();
            }
        }
        throw new AlgoliaException('Hosts unreachable: '.implode(',', $exceptions));
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
        $readTimeout
    ) {
        if (strpos($host, 'http') === 0) {
            $url = $host.$path;
        } else {
            $url = 'https://'.$host.$path;
        }

        if ($params != null && count($params) > 0) {
            $params2 = [];
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    $params2[$key] = json_encode($val);
                } else {
                    $params2[$key] = $val;
                }
            }
            $url .= '?'.http_build_query($params2);
        }

        // initialize curl library
        $curlHandle = curl_init();

        // set curl options
        try {
            foreach ($this->curlOptions as $curlOption => $optionValue) {
                curl_setopt($curlHandle, constant($curlOption), $optionValue);
            }
        } catch (\Exception $e) {
            $this->invalidOptions($this->curlOptions, $e->getMessage());
        }

        //curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
        if ($context->adminAPIKey == null) {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array_merge([
                        'X-Algolia-Application-Id: '.$context->applicationID,
                        'X-Algolia-API-Key: '.$context->apiKey,
                        'Content-type: application/json',
                        ], $context->headers));
        } else {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array_merge([
                    'X-Algolia-Application-Id: '.$context->applicationID,
                    'X-Algolia-API-Key: '.$context->adminAPIKey,
                    'X-Forwarded-For: '.$context->endUserIP,
                    'X-Algolia-UserToken: '.$context->algoliaUserToken,
                    'X-Forwarded-API-Key: '.$context->rateLimitAPIKey,
                    'Content-type: application/json',
                    ], $context->headers));
        }

        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Algolia for PHP '.Version::get());
        //Return the output instead of printing it
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_CAINFO, $this->caInfoPath);

        curl_setopt($curlHandle, CURLOPT_URL, $url);
        $version = curl_version();
        if (version_compare(phpversion(), '5.2.3', '>=') && version_compare($version['version'], '7.16.2', '>=') && $connectTimeout < 1) {
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeout * 1000);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, $readTimeout * 1000);
        } else {
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, $readTimeout);
        }

        // The problem is that on (Li|U)nix, when libcurl uses the standard name resolver,
        // a SIGALRM is raised during name resolution which libcurl thinks is the timeout alarm.
        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

        if ($method === 'GET') {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_POST, false);
        } else {
            if ($method === 'POST') {
                $body = ($data) ? json_encode($data) : '';
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curlHandle, CURLOPT_POST, true);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
            } elseif ($method === 'DELETE') {
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curlHandle, CURLOPT_POST, false);
            } elseif ($method === 'PUT') {
                $body = ($data) ? json_encode($data) : '';
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
                curl_setopt($curlHandle, CURLOPT_POST, true);
            }
        }
        $mhandle = $context->getMHandle($curlHandle);

        // Do all the processing.
        $running = null;
        do {
            $mrc = curl_multi_exec($mhandle, $running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mhandle, 0.1) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mhandle, $running);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        $http_status = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $response = curl_multi_getcontent($curlHandle);
        $error = curl_error($curlHandle);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        if ($http_status === 0 || $http_status === 503) {
            // Could not reach host or service unavailable, try with another one if we have it
            $context->releaseMHandle($curlHandle);
            curl_close($curlHandle);

            return;
        }

        $answer = json_decode($response, true);
        $context->releaseMHandle($curlHandle);
        curl_close($curlHandle);

        if (intval($http_status / 100) == 4) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : $http_status + ' error');
        } elseif (intval($http_status / 100) != 2) {
            throw new \Exception($http_status.': '.$response);
        }

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
            // PHP 5.3 less than 1.2.2 (Ubuntu 10.04 LTS)
            case defined('JSON_ERROR_UTF8') ? JSON_ERROR_UTF8 : -1:
                $errorMsg = 'JSON parsing error: malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            case JSON_ERROR_NONE:
            default:
                $errorMsg = null;
                break;
        }
        if ($errorMsg !== null) {
            throw new AlgoliaException($errorMsg);
        }

        return $answer;
    }

    /**
     * Checks if curl option passed are valid curl options.
     *
     * @param array $curlOptions must be array but no type required while first test throw clear Exception
     *
     * @return array
     */
    protected function checkCurlOptions($curlOptions)
    {
        if (!is_array($curlOptions)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'AlgoliaSearch requires %s option to be array of valid curl options.',
                    static::CURLOPT
                )
            );
        }

        $checkedCurlOptions = array_intersect(array_keys($curlOptions), array_keys($this->getCurlConstants()));

        if (count($checkedCurlOptions) !== count($curlOptions)) {
            $this->invalidOptions($curlOptions);
        }

        return $curlOptions;
    }

    /**
     * Get all php curl available options.
     *
     * @return array
     */
    protected function getCurlConstants()
    {
        if (!is_null($this->curlConstants)) {
            return $this->curlConstants;
        }

        $curlAllConstants = get_defined_constants(true);

        if (isset($curlAllConstants['curl'])) {
            $curlAllConstants = $curlAllConstants['curl'];
        } elseif (isset($curlAllConstants['Core'])) { // hhvm
            $curlAllConstants = $curlAllConstants['Core'];
        } else {
            return $this->curlConstants;
        }

        $curlConstants = [];
        foreach ($curlAllConstants as $constantName => $constantValue) {
            if (strpos($constantName, 'CURLOPT') === 0) {
                $curlConstants[$constantName] = $constantValue;
            }
        }

        $this->curlConstants = $curlConstants;

        return $this->curlConstants;
    }

    /**
     * throw clear Exception when bad curl option is set.
     *
     * @param array  $curlOptions
     * @param string $errorMsg    add specific message for disambiguation
     */
    protected function invalidOptions(array $curlOptions = [], $errorMsg = '')
    {
        throw new \OutOfBoundsException(
            sprintf(
                'AlgoliaSearch %s options keys are invalid. %s given. error message : %s',
                static::CURLOPT,
                json_encode($curlOptions),
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
     * @param string $appId
     * @param string $apiKey
     * @param array  $hostsArray
     * @param array  $options
     *
     * @return mixed
     */
    public static function initPlaces($appId, $apiKey, $hostsArray = null, $options = [])
    {
        $options['placesEnabled'] = true;
        $client = new static($appId, $apiKey, $hostsArray, $options);

        return $client->getPlacesIndex();
    }
}

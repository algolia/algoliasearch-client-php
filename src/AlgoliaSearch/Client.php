<?php

namespace AlgoliaSearch;

/**
 * Entry point in the PHP API.
 * You should instantiate a Client object with your ApplicationID, ApiKey and Hosts
 * to start using Algolia Search API
 */
class Client
{
    /**
     * @var AlgoliaSearch\ClientContext
     */
    protected $context;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var \GuzzleHttp\Adaptor\MultiAdapter
     */
    protected $multiAdapter;

    /**
     * @var \GuzzleHttp\Message\MessageFactory
     */
    protected $messageFactory;

    /**
     * Algolia Search initialization
     *
     * @param applicationID the application ID you have in your admin interface
     * @param apiKey a valid API key for the service
     * @param hostsArray the list of hosts that you have received for the service
     */
    public function __construct($applicationID, $apiKey, $hostsArray = null)
    {

        if ($hostsArray == null) {
            $this->context = new ClientContext($applicationID, $apiKey, array(
                $applicationID . "-1.algolia.io",
                $applicationID . "-2.algolia.io",
                $applicationID . "-3.algolia.io"
            ));
        } else {
            $this->context = new ClientContext($applicationID, $apiKey, $hostsArray);
        }

        $this->messageFactory = new \GuzzleHttp\Message\MessageFactory();
        $this->multiAdapter = new \GuzzleHttp\Adapter\Curl\MultiAdapter($this->messageFactory);
        $this->httpClient = new \GuzzleHttp\Client(
            array(
                'message_factory' => $this->messageFactory,
                'adapter' => $this->multiAdapter,
                'defaults' => array(
                    'verify' => __DIR__ . '/../../resources/ca-bundle.crt'
                )
            )
        );
    }

    /**
     * Allow to use IP rate limit when you have a proxy between end-user and Algolia.
     * This option will set the X-Forwarded-For HTTP header with the client IP and the X-Forwarded-API-Key with the API Key having rate limits.
     *
     * @param adminAPIKey the admin API Key you can find in your dashboard
     * @param endUserIP the end user IP (you can use both IPV4 or IPV6 syntax)
     * @param rateLimitAPIKey the API key on which you have a rate limit
     */
    public function enableRateLimitForward($adminAPIKey, $endUserIP, $rateLimitAPIKey)
    {
        $this->context->setRateLimit($adminAPIKey, $endUserIP, $rateLimitAPIKey);
    }

    /**
     * Disable IP rate limit enabled with enableRateLimitForward() function
     */
    public function disableRateLimitForward()
    {
        $this->context->disableRateLimit();
    }

    /**
     * This method allows to query multiple indexes with one API call
     */
    public function multipleQueries($queries, $indexNameKey = "indexName")
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
            foreach ($query as $key => $value) {
              if (gettype($value) == "array") {
                $query[$key] = json_encode($value);
              }
            }
            $req = array("indexName" => $indexes, "params" => http_build_query($query));
            array_push($requests, $req);
        }
        return $this->request($this->context, "POST", "/1/indexes/*/queries", array(), array("requests" => $requests));
    }

    /**
     * List all existing indexes
     * return an object in the form:
     * array("items" => array(
     *                        array("name" => "contacts", "createdAt" => "2013-01-18T15:33:13.556Z"),
     *                        array("name" => "notes", "createdAt" => "2013-01-18T15:33:13.556Z")
     *                        ))
     */
    public function listIndexes()
    {
        return $this->request($this->context, "GET", "/1/indexes/");
    }

    /**
     * Delete an index
     *
     * @param indexName the name of index to delete
     * return an object containing a "deletedAt" attribute
     */
    public function deleteIndex($indexName)
    {
        return $this->request($this->context, "DELETE", "/1/indexes/" . urlencode($indexName));
    }

    /**
     * Move an existing index.
     *
     * @param srcIndexName the name of index to copy.
     * @param dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overriten if it already exist).
     */
    public function moveIndex($srcIndexName, $dstIndexName)
    {
        $request = array("operation" => "move", "destination" => $dstIndexName);
        return $this->request($this->context, "POST", "/1/indexes/" . urlencode($srcIndexName) . "/operation", array(), $request);
    }

    /**
     * Copy an existing index.
     *
     * @param srcIndexName the name of index to copy.
     * @param dstIndexName the new index name that will contains a copy of srcIndexName (destination will be overriten if it already exist).
     */
    public function copyIndex($srcIndexName, $dstIndexName)
    {
        $request = array("operation" => "copy", "destination" => $dstIndexName);
        return $this->request($this->context, "POST", "/1/indexes/" . urlencode($srcIndexName) . "/operation", array(), $request);
    }

    /**
     * Return last logs entries.
     *
     * @param offset Specify the first entry to retrieve (0-based, 0 is the most recent log entry).
     * @param length Specify the maximum number of entries to retrieve starting at offset. Maximum allowed value: 1000.
     */
    public function getLogs($offset = 0, $length = 10, $onlyErrors = false)
    {
        return $this->request($this->context, "GET", "/1/logs?offset=" . $offset . "&length=" . $length . "&onlyErrors=" . $onlyErrors);
    }

    /**
     * Get the index object initialized (no server call needed for initialization)
     *
     * @param indexName the name of index
     */
    public function initIndex($indexName)
    {
        return new Index($this->context, $this, $indexName);
    }

    /**
     * List all existing user keys with their associated ACLs
     *
     */
    public function listUserKeys()
    {
        return $this->request($this->context, "GET", "/1/keys");
    }

    /**
     * Get ACL of a user key
     *
     */
    public function getUserKeyACL($key)
    {
        return $this->request($this->context, "GET", "/1/keys/" . $key);
    }

    /**
     * Delete an existing user key
     *
     */
    public function deleteUserKey($key)
    {
        return $this->request($this->context, "DELETE", "/1/keys/" . $key);
    }

    /**
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
    public function addUserKey($acls, $validity = 0, $maxQueriesPerIPPerHour = 0, $maxHitsPerQuery = 0, $indexes = null)
    {
        $params = array(
            "acl" => $acls,
            "validity" => $validity,
            "maxQueriesPerIPPerHour" => $maxQueriesPerIPPerHour,
            "maxHitsPerQuery" => $maxHitsPerQuery
        );
        if ($indexes != null) {
            if (is_array($indexes)) {
                $tmp = array();
                foreach ($indexes as $index) {
                    array_push($tmp, $index);
                }
                $indexes = join(',', $tmp);
            }
            $params['indexes'] = $indexes;
        }

        return $this->request($this->context, "POST", "/1/keys", array(), $params);
    }

    /**
     * Generate a secured and public API Key from a list of tagFilters and an
     * optional user token identifying the current user
     *
     * @param privateApiKey your private API Key
     * @param tagFilters the list of tags applied to the query (used as security)
     * @param userToken an optional token identifying the current user
     *
     */
    public function generateSecuredApiKey($privateApiKey, $tagFilters, $userToken = null)
    {
        if (is_array($tagFilters)) {
            $tmp = array();
            foreach ($tagFilters as $tag) {
                if (is_array($tag)) {
                    $tmp2 = array();
                    foreach ($tag as $tag2) {
                        array_push($tmp2, $tag2);
                    }
                    array_push($tmp, '(' . join(',', $tmp2) . ')');
                } else {
                    array_push($tmp, $tag);
                }
            }
            $tagFilters = join(',', $tmp);
        }
        return hash_hmac('sha256', $tagFilters . $userToken, $privateApiKey);
    }

    public function request($context, $method, $path, $params = array(), $data = array())
    {
        $exception = null;
        foreach ($context->hostsArray as &$host) {
            try {
                $res = $this->doRequest($context, $method, $host, $path, $params, $data);
                if ($res !== null)
                    return $res;
            } catch (AlgoliaException $e) {
                throw $e;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        if ($exception == null) {
            throw new AlgoliaException('Hosts unreachable');
        } else {
            throw $exception;
        }
    }

    private function doRequest($context, $method, $host, $path, $params, $data)
    {
        $request = $this->httpClient->createRequest(
            $method,
            "https://" . $host . $path,
            array(
                'query' => $params,
                'json' => $data,
                'config' => array(
                    'curl' => array(
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FAILONERROR => false,
                        CURLOPT_ENCODING => '',
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_SSL_VERIFYHOST => 2,
                        CURLOPT_CAINFO => __DIR__ . '/resources/ca-bundle.crt',
                        CURLOPT_CONNECTTIMEOUT => 30,
                        CURLOPT_NOSIGNAL => 1
                    )
                )
            )
        );

        $request->setHeader('Content-type', 'application/json');
        $request->setHeader('User-Agent', 'Algolia for PHP 1.1.9');
        $request->setHeader('X-Algolia-Application-Id', $context->applicationID);

        if ($context->adminAPIKey === null) {
            $request->setHeader('X-Algolia-API-Key', $context->apiKey);
        } else {
            $request->setHeader('X-Algolia-API-Key', $context->adminAPIKey);
            $request->setHeader('X-Forwarded-For', $context->endUserIP);
            $request->setHeader('X-Forwarded-API-Key', $context->rateLimitAPIKey);
        }

        $response = $this->httpClient->send($request);

        $http_status = $response->getStatusCode();

        $response = $response->getBody();

        if ($http_status === 0 || $http_status === 503) {
            // Could not reach host or service unavailable, try with another one if we have it
            return null;
        }

        $answer = json_decode($response, true);

        if ($http_status == 400) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Bad request");
        } elseif ($http_status === 403) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Invalid Application-ID or API-Key");
        } elseif ($http_status === 404) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Resource does not exist");
        } elseif ($http_status != 200 && $http_status != 201) {
            throw new \Exception($http_status . ": " . $response);
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
            case (defined('JSON_ERROR_UTF8') ? JSON_ERROR_UTF8 : -1): // PHP 5.3 less than 5.3.3 (Ubuntu 10.04 LTS)
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
}

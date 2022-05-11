<?php

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\QuerySuggestionsConfig;
use Algolia\AlgoliaSearch\ObjectSerializer;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

/**
 * QuerySuggestionsClient Class Doc Comment
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class QuerySuggestionsClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var QuerySuggestionsConfig
     */
    protected $config;

    /**
     * @param QuerySuggestionsConfig $config
     * @param ApiWrapperInterface $apiWrapper
     */
    public function __construct(
        ApiWrapperInterface $apiWrapper,
        QuerySuggestionsConfig $config
    ) {
        $this->config = $config;

        $this->api = $apiWrapper;
    }

    /**
     * Instantiate the client with basic credentials and region
     *
     * @param string $appId  Application ID
     * @param string $apiKey Algolia API Key
     * @param string $region Region
     */
    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $allowedRegions = ['eu', 'us'];
        $config = QuerySuggestionsConfig::create(
            $appId,
            $apiKey,
            $region,
            $allowedRegions
        );

        return static::createWithConfig($config);
    }

    /**
     * Instantiate the client with configuration
     *
     * @param QuerySuggestionsConfig $config Configuration
     */
    public static function createWithConfig(QuerySuggestionsConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $url = str_replace(
                '{region}',
                $config->getRegion(),
                'query-suggestions.{region}.algolia.com'
            );
            $clusterHosts = ClusterHosts::create($url);
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    /**
     * @return QuerySuggestionsConfig
     */
    public function getClientConfig()
    {
        return $this->config;
    }

    /**
     * Create a configuration.
     *
     * @param array $querySuggestionsIndexWithIndexParam querySuggestionsIndexWithIndexParam (required)
     *
     * @see \Algolia\AlgoliaSearch\Model\QuerySuggestions\QuerySuggestionsIndexWithIndexParam
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\SucessResponse
     */
    public function createConfig(
        $querySuggestionsIndexWithIndexParam,
        $requestOptions = []
    ) {
        // verify the required parameter 'querySuggestionsIndexWithIndexParam' is set
        if (
            $querySuggestionsIndexWithIndexParam === null ||
            (is_array($querySuggestionsIndexWithIndexParam) &&
                count($querySuggestionsIndexWithIndexParam) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $querySuggestionsIndexWithIndexParam when calling createConfig'
            );
        }

        $resourcePath = '/1/configs';
        $queryParameters = [];
        $httpBody = [];

        if (isset($querySuggestionsIndexWithIndexParam)) {
            $httpBody = $querySuggestionsIndexWithIndexParam;
        }
        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Send requests to the Algolia REST API.
     *
     * @param string $path The path of the API endpoint to target, anything after the /1 needs to be specified. (required)
     * @param array $parameters Query parameters to be applied to the current query. (optional)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|object
     */
    public function del($path, $parameters = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if ($path === null || (is_array($path) && count($path) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $path when calling del'
            );
        }

        $resourcePath = '/1{path}';
        $queryParameters = [];
        $httpBody = [];

        if ($parameters !== null) {
            if (
                is_array($parameters) &&
                !in_array(
                    'parameters',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($parameters as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Delete a configuration.
     *
     * @param string $indexName The index in which to perform the request. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\SucessResponse
     */
    public function deleteConfig($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (
            $indexName === null ||
            (is_array($indexName) && count($indexName) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $indexName when calling deleteConfig'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $httpBody = [];

        // path params
        if ($indexName !== null) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Send requests to the Algolia REST API.
     *
     * @param string $path The path of the API endpoint to target, anything after the /1 needs to be specified. (required)
     * @param array $parameters Query parameters to be applied to the current query. (optional)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|object
     */
    public function get($path, $parameters = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if ($path === null || (is_array($path) && count($path) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $path when calling get'
            );
        }

        $resourcePath = '/1{path}';
        $queryParameters = [];
        $httpBody = [];

        if ($parameters !== null) {
            if (
                is_array($parameters) &&
                !in_array(
                    'parameters',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($parameters as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * List configurations.
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\QuerySuggestionsIndex[]
     */
    public function getAllConfigs($requestOptions = [])
    {
        $resourcePath = '/1/configs';
        $queryParameters = [];
        $httpBody = [];

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get a single configuration.
     *
     * @param string $indexName The index in which to perform the request. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\QuerySuggestionsIndex
     */
    public function getConfig($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (
            $indexName === null ||
            (is_array($indexName) && count($indexName) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $indexName when calling getConfig'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $httpBody = [];

        // path params
        if ($indexName !== null) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get configuration status.
     *
     * @param string $indexName The index in which to perform the request. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\Status
     */
    public function getConfigStatus($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (
            $indexName === null ||
            (is_array($indexName) && count($indexName) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $indexName when calling getConfigStatus'
            );
        }

        $resourcePath = '/1/configs/{indexName}/status';
        $queryParameters = [];
        $httpBody = [];

        // path params
        if ($indexName !== null) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get a log file.
     *
     * @param string $indexName The index in which to perform the request. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\LogFile[]
     */
    public function getLogFile($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (
            $indexName === null ||
            (is_array($indexName) && count($indexName) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $indexName when calling getLogFile'
            );
        }

        $resourcePath = '/1/logs/{indexName}';
        $queryParameters = [];
        $httpBody = [];

        // path params
        if ($indexName !== null) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Send requests to the Algolia REST API.
     *
     * @param string $path The path of the API endpoint to target, anything after the /1 needs to be specified. (required)
     * @param array $parameters Query parameters to be applied to the current query. (optional)
     * @param array $body The parameters to send with the custom request. (optional)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|object
     */
    public function post(
        $path,
        $parameters = null,
        $body = null,
        $requestOptions = []
    ) {
        // verify the required parameter 'path' is set
        if ($path === null || (is_array($path) && count($path) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $path when calling post'
            );
        }

        $resourcePath = '/1{path}';
        $queryParameters = [];
        $httpBody = [];

        if ($parameters !== null) {
            if (
                is_array($parameters) &&
                !in_array(
                    'parameters',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($parameters as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        if (isset($body)) {
            $httpBody = $body;
        }
        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Send requests to the Algolia REST API.
     *
     * @param string $path The path of the API endpoint to target, anything after the /1 needs to be specified. (required)
     * @param array $parameters Query parameters to be applied to the current query. (optional)
     * @param array $body The parameters to send with the custom request. (optional)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|object
     */
    public function put(
        $path,
        $parameters = null,
        $body = null,
        $requestOptions = []
    ) {
        // verify the required parameter 'path' is set
        if ($path === null || (is_array($path) && count($path) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $path when calling put'
            );
        }

        $resourcePath = '/1{path}';
        $queryParameters = [];
        $httpBody = [];

        if ($parameters !== null) {
            if (
                is_array($parameters) &&
                !in_array(
                    'parameters',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($parameters as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        if (isset($body)) {
            $httpBody = $body;
        }
        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'PUT',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Update a configuration.
     *
     * @param string $indexName The index in which to perform the request. (required)
     * @param array $querySuggestionsIndexParam querySuggestionsIndexParam (required)
     * - $querySuggestionsIndexParam['sourceIndices'] => (array) List of source indices used to generate a Query Suggestions index. (required)
     * - $querySuggestionsIndexParam['languages'] => (array) De-duplicate singular and plural suggestions. For example, let's say your index contains English content, and that two suggestions “shoe” and “shoes” end up in your Query Suggestions index. If the English language is configured, only the most popular of those two suggestions would remain.
     * - $querySuggestionsIndexParam['exclude'] => (array) List of words and patterns to exclude from the Query Suggestions index.
     *
     * @see \Algolia\AlgoliaSearch\Model\QuerySuggestions\QuerySuggestionsIndexParam
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\QuerySuggestions\SucessResponse
     */
    public function updateConfig(
        $indexName,
        $querySuggestionsIndexParam,
        $requestOptions = []
    ) {
        // verify the required parameter 'indexName' is set
        if (
            $indexName === null ||
            (is_array($indexName) && count($indexName) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $indexName when calling updateConfig'
            );
        }
        // verify the required parameter 'querySuggestionsIndexParam' is set
        if (
            $querySuggestionsIndexParam === null ||
            (is_array($querySuggestionsIndexParam) &&
                count($querySuggestionsIndexParam) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $querySuggestionsIndexParam when calling updateConfig'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $httpBody = [];

        // path params
        if ($indexName !== null) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        if (isset($querySuggestionsIndexParam)) {
            $httpBody = $querySuggestionsIndexParam;
        }
        $requestOptions += $queryParameters;

        return $this->sendRequest(
            'PUT',
            $resourcePath,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    private function sendRequest(
        $method,
        $resourcePath,
        $queryParameters,
        $httpBody,
        $requestOptions
    ) {
        $query = \GuzzleHttp\Psr7\Query::build($queryParameters);

        if ($method === 'GET') {
            $request = $this->api->read(
                $method,
                $resourcePath . ($query ? "?{$query}" : ''),
                $requestOptions
            );
        } else {
            $request = $this->api->write(
                $method,
                $resourcePath . ($query ? "?{$query}" : ''),
                $httpBody,
                $requestOptions
            );
        }

        return $request;
    }
}

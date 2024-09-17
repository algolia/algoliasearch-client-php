<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\QuerySuggestionsConfig;
use Algolia\AlgoliaSearch\Model\QuerySuggestions\Configuration;
use Algolia\AlgoliaSearch\Model\QuerySuggestions\ConfigurationWithIndex;
use Algolia\AlgoliaSearch\ObjectSerializer;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use GuzzleHttp\Psr7\Query;

/**
 * QuerySuggestionsClient Class Doc Comment.
 *
 * @category Class
 */
class QuerySuggestionsClient
{
    public const VERSION = '4.4.2';

    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var QuerySuggestionsConfig
     */
    protected $config;

    public function __construct(ApiWrapperInterface $apiWrapper, QuerySuggestionsConfig $config)
    {
        $this->config = $config;
        $this->api = $apiWrapper;
    }

    /**
     * Instantiate the client with basic credentials and region.
     *
     * @param string $appId  Application ID
     * @param string $apiKey Algolia API Key
     * @param string $region Region
     */
    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $config = QuerySuggestionsConfig::create($appId, $apiKey, $region);

        return static::createWithConfig($config);
    }

    /**
     * Instantiate the client with configuration.
     *
     * @param QuerySuggestionsConfig $config Configuration
     */
    public static function createWithConfig(QuerySuggestionsConfig $config)
    {
        $config = clone $config;

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            self::getClusterHosts($config)
        );

        return new static($apiWrapper, $config);
    }

    /**
     * Gets the cluster hosts depending on the config.
     *
     * @return ClusterHosts
     */
    public static function getClusterHosts(QuerySuggestionsConfig $config)
    {
        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $url = null !== $config->getRegion() && '' !== $config->getRegion() ?
                str_replace('{region}', $config->getRegion(), 'query-suggestions.{region}.algolia.com') :
                '';
            $clusterHosts = ClusterHosts::create($url);
        }

        return $clusterHosts;
    }

    /**
     * @return QuerySuggestionsConfig
     */
    public function getClientConfig()
    {
        return $this->config;
    }

    /**
     * Stub method setting a new API key to authenticate requests.
     *
     * @param string $apiKey
     */
    public function setClientApiKey($apiKey)
    {
        $this->config->setClientApiKey($apiKey);
    }

    /**
     * Creates a new Query Suggestions configuration.  You can have up to 100 configurations per Algolia application.
     *
     * Required API Key ACLs:
     *  - editSettings
     *
     * @param array $configurationWithIndex configurationWithIndex (required)
     *
     * @see ConfigurationWithIndex
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\BaseResponse|array<string, mixed>
     */
    public function createConfig($configurationWithIndex, $requestOptions = [])
    {
        // verify the required parameter 'configurationWithIndex' is set
        if (!isset($configurationWithIndex)) {
            throw new \InvalidArgumentException(
                'Parameter `configurationWithIndex` is required when calling `createConfig`.'
            );
        }

        $resourcePath = '/1/configs';
        $queryParameters = [];
        $headers = [];
        $httpBody = $configurationWithIndex;

        return $this->sendRequest('POST', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * This method allow you to send requests to the Algolia REST API.
     *
     * @param string $path           Path of the endpoint, anything after \&quot;/1\&quot; must be specified. (required)
     * @param array  $parameters     Query parameters to apply to the current query. (optional)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|object
     */
    public function customDelete($path, $parameters = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if (!isset($path)) {
            throw new \InvalidArgumentException(
                'Parameter `path` is required when calling `customDelete`.'
            );
        }

        $resourcePath = '/{path}';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        if (null !== $parameters) {
            $queryParameters = $parameters;
        }

        // path params
        if (null !== $path) {
            $resourcePath = str_replace(
                '{path}',
                $path,
                $resourcePath
            );
        }

        return $this->sendRequest('DELETE', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * This method allow you to send requests to the Algolia REST API.
     *
     * @param string $path           Path of the endpoint, anything after \&quot;/1\&quot; must be specified. (required)
     * @param array  $parameters     Query parameters to apply to the current query. (optional)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|object
     */
    public function customGet($path, $parameters = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if (!isset($path)) {
            throw new \InvalidArgumentException(
                'Parameter `path` is required when calling `customGet`.'
            );
        }

        $resourcePath = '/{path}';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        if (null !== $parameters) {
            $queryParameters = $parameters;
        }

        // path params
        if (null !== $path) {
            $resourcePath = str_replace(
                '{path}',
                $path,
                $resourcePath
            );
        }

        return $this->sendRequest('GET', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * This method allow you to send requests to the Algolia REST API.
     *
     * @param string $path           Path of the endpoint, anything after \&quot;/1\&quot; must be specified. (required)
     * @param array  $parameters     Query parameters to apply to the current query. (optional)
     * @param array  $body           Parameters to send with the custom request. (optional)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|object
     */
    public function customPost($path, $parameters = null, $body = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if (!isset($path)) {
            throw new \InvalidArgumentException(
                'Parameter `path` is required when calling `customPost`.'
            );
        }

        $resourcePath = '/{path}';
        $queryParameters = [];
        $headers = [];
        $httpBody = isset($body) ? $body : [];

        if (null !== $parameters) {
            $queryParameters = $parameters;
        }

        // path params
        if (null !== $path) {
            $resourcePath = str_replace(
                '{path}',
                $path,
                $resourcePath
            );
        }

        return $this->sendRequest('POST', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * This method allow you to send requests to the Algolia REST API.
     *
     * @param string $path           Path of the endpoint, anything after \&quot;/1\&quot; must be specified. (required)
     * @param array  $parameters     Query parameters to apply to the current query. (optional)
     * @param array  $body           Parameters to send with the custom request. (optional)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|object
     */
    public function customPut($path, $parameters = null, $body = null, $requestOptions = [])
    {
        // verify the required parameter 'path' is set
        if (!isset($path)) {
            throw new \InvalidArgumentException(
                'Parameter `path` is required when calling `customPut`.'
            );
        }

        $resourcePath = '/{path}';
        $queryParameters = [];
        $headers = [];
        $httpBody = isset($body) ? $body : [];

        if (null !== $parameters) {
            $queryParameters = $parameters;
        }

        // path params
        if (null !== $path) {
            $resourcePath = str_replace(
                '{path}',
                $path,
                $resourcePath
            );
        }

        return $this->sendRequest('PUT', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Deletes a Query Suggestions configuration.  Deleting only removes the configuration and stops updates to the Query Suggestions index. To delete the Query Suggestions index itself, use the Search API and the `Delete an index` operation.
     *
     * Required API Key ACLs:
     *  - editSettings
     *
     * @param string $indexName      Query Suggestions index name. (required)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\BaseResponse|array<string, mixed>
     */
    public function deleteConfig($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (!isset($indexName)) {
            throw new \InvalidArgumentException(
                'Parameter `indexName` is required when calling `deleteConfig`.'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        // path params
        if (null !== $indexName) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        return $this->sendRequest('DELETE', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Retrieves all Query Suggestions configurations of your Algolia application.
     *
     * Required API Key ACLs:
     *  - settings
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\ConfigurationResponse[]|array<string, mixed>
     */
    public function getAllConfigs($requestOptions = [])
    {
        $resourcePath = '/1/configs';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        return $this->sendRequest('GET', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Retrieves a single Query Suggestions configuration by its index name.
     *
     * Required API Key ACLs:
     *  - settings
     *
     * @param string $indexName      Query Suggestions index name. (required)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\ConfigurationResponse|array<string, mixed>
     */
    public function getConfig($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (!isset($indexName)) {
            throw new \InvalidArgumentException(
                'Parameter `indexName` is required when calling `getConfig`.'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        // path params
        if (null !== $indexName) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        return $this->sendRequest('GET', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Reports the status of a Query Suggestions index.
     *
     * Required API Key ACLs:
     *  - settings
     *
     * @param string $indexName      Query Suggestions index name. (required)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\ConfigStatus|array<string, mixed>
     */
    public function getConfigStatus($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (!isset($indexName)) {
            throw new \InvalidArgumentException(
                'Parameter `indexName` is required when calling `getConfigStatus`.'
            );
        }

        $resourcePath = '/1/configs/{indexName}/status';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        // path params
        if (null !== $indexName) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        return $this->sendRequest('GET', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Retrieves the logs for a single Query Suggestions index.
     *
     * Required API Key ACLs:
     *  - settings
     *
     * @param string $indexName      Query Suggestions index name. (required)
     * @param array  $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\LogFile|array<string, mixed>
     */
    public function getLogFile($indexName, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (!isset($indexName)) {
            throw new \InvalidArgumentException(
                'Parameter `indexName` is required when calling `getLogFile`.'
            );
        }

        $resourcePath = '/1/logs/{indexName}';
        $queryParameters = [];
        $headers = [];
        $httpBody = null;

        // path params
        if (null !== $indexName) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        return $this->sendRequest('GET', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    /**
     * Updates a QuerySuggestions configuration.
     *
     * Required API Key ACLs:
     *  - editSettings
     *
     * @param string $indexName     Query Suggestions index name. (required)
     * @param array  $configuration configuration (required)
     *                              - $configuration['sourceIndices'] => (array) Algolia indices from which to get the popular searches for query suggestions. (required)
     *                              - $configuration['languages'] => (array)
     *                              - $configuration['exclude'] => (array)
     *                              - $configuration['enablePersonalization'] => (bool) Whether to turn on personalized query suggestions.
     *                              - $configuration['allowSpecialCharacters'] => (bool) Whether to include suggestions with special characters.
     *
     * @see Configuration
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return \Algolia\AlgoliaSearch\Model\QuerySuggestions\BaseResponse|array<string, mixed>
     */
    public function updateConfig($indexName, $configuration, $requestOptions = [])
    {
        // verify the required parameter 'indexName' is set
        if (!isset($indexName)) {
            throw new \InvalidArgumentException(
                'Parameter `indexName` is required when calling `updateConfig`.'
            );
        }
        // verify the required parameter 'configuration' is set
        if (!isset($configuration)) {
            throw new \InvalidArgumentException(
                'Parameter `configuration` is required when calling `updateConfig`.'
            );
        }

        $resourcePath = '/1/configs/{indexName}';
        $queryParameters = [];
        $headers = [];
        $httpBody = $configuration;

        // path params
        if (null !== $indexName) {
            $resourcePath = str_replace(
                '{indexName}',
                ObjectSerializer::toPathValue($indexName),
                $resourcePath
            );
        }

        return $this->sendRequest('PUT', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions);
    }

    private function sendRequest($method, $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions, $useReadTransporter = false)
    {
        if (!isset($requestOptions['headers'])) {
            $requestOptions['headers'] = [];
        }
        if (!isset($requestOptions['queryParameters'])) {
            $requestOptions['queryParameters'] = [];
        }

        $requestOptions['headers'] = array_merge($headers, $requestOptions['headers']);
        $requestOptions['queryParameters'] = array_merge($queryParameters, $requestOptions['queryParameters']);
        $query = Query::build($requestOptions['queryParameters']);

        return $this->api->sendRequest(
            $method,
            $resourcePath.($query ? "?{$query}" : ''),
            $httpBody,
            $requestOptions,
            $useReadTransporter
        );
    }
}

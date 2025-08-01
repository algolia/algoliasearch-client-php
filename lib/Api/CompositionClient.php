<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\CompositionConfig;
use Algolia\AlgoliaSearch\Model\Composition\RequestBody;
use Algolia\AlgoliaSearch\Model\Composition\SearchForFacetValuesRequest;
use Algolia\AlgoliaSearch\Model\Composition\SearchForFacetValuesResponse;
use Algolia\AlgoliaSearch\Model\Composition\SearchResponse;
use Algolia\AlgoliaSearch\ObjectSerializer;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use GuzzleHttp\Psr7\Query;

/**
 * CompositionClient Class Doc Comment.
 *
 * @category Class
 */
class CompositionClient
{
    public const VERSION = '4.26.0';

    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var IngestionClient
     */
    protected $ingestionTransporter;

    /**
     * @var CompositionConfig
     */
    protected $config;

    public function __construct(ApiWrapperInterface $apiWrapper, CompositionConfig $config)
    {
        $this->config = $config;
        $this->api = $apiWrapper;
    }

    /**
     * Instantiate the client with basic credentials.
     *
     * @param string $appId  Application ID
     * @param string $apiKey Algolia API Key
     */
    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(CompositionConfig::create($appId, $apiKey));
    }

    /**
     * Instantiate the client with configuration.
     *
     * @param CompositionConfig $config Configuration
     */
    public static function createWithConfig(CompositionConfig $config)
    {
        $config = clone $config;

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            self::getClusterHosts($config)
        );

        $client = new static($apiWrapper, $config);

        return $client;
    }

    /**
     * Gets the cluster hosts depending on the config.
     *
     * @return ClusterHosts
     */
    public static function getClusterHosts(CompositionConfig $config)
    {
        $cacheKey = sprintf('%s-clusterHosts-%s', __CLASS__, $config->getAppId());

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } elseif (false === ($clusterHosts = ClusterHosts::createFromCache($cacheKey))) {
            // We'll try to restore the ClusterHost from cache, if we cannot
            // we create a new instance and set the cache key
            $clusterHosts = ClusterHosts::createFromAppId($config->getAppId())
                ->setCacheKey($cacheKey)
            ;
        }

        return $clusterHosts;
    }

    /**
     * @return CompositionConfig
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
     * Runs a query on a single composition and returns matching results.
     *
     * Required API Key ACLs:
     *  - search
     *
     * @param string            $compositionID Unique Composition ObjectID. (required)
     * @param array|RequestBody $requestBody   requestBody (required)
     *                                         - $requestBody['params'] => (array)
     *
     * @see RequestBody
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|SearchResponse
     */
    public function search($compositionID, $requestBody, $requestOptions = [])
    {
        // verify the required parameter 'compositionID' is set
        if (!isset($compositionID)) {
            throw new \InvalidArgumentException(
                'Parameter `compositionID` is required when calling `search`.'
            );
        }
        // verify the required parameter 'requestBody' is set
        if (!isset($requestBody)) {
            throw new \InvalidArgumentException(
                'Parameter `requestBody` is required when calling `search`.'
            );
        }

        $resourcePath = '/1/compositions/{compositionID}/run';
        $queryParameters = [];
        $headers = [];
        $httpBody = $requestBody;

        // path params
        if (null !== $compositionID) {
            $resourcePath = str_replace(
                '{compositionID}',
                ObjectSerializer::toPathValue($compositionID),
                $resourcePath
            );
        }

        return $this->sendRequest('POST', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions, true);
    }

    /**
     * Searches for values of a specified facet attribute on the composition's main source's index.  - By default, facet values are sorted by decreasing count.   You can adjust this with the `sortFacetValueBy` parameter. - Searching for facet values doesn't work if you have **more than 65 searchable facets and searchable attributes combined**.
     *
     * Required API Key ACLs:
     *  - search
     *
     * @param string                            $compositionID               Unique Composition ObjectID. (required)
     * @param string                            $facetName                   Facet attribute in which to search for values.  This attribute must be included in the `attributesForFaceting` index setting with the `searchable()` modifier. (required)
     * @param array|SearchForFacetValuesRequest $searchForFacetValuesRequest searchForFacetValuesRequest (optional)
     *                                                                       - $searchForFacetValuesRequest['params'] => (array)
     *
     * @see SearchForFacetValuesRequest
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|SearchForFacetValuesResponse
     */
    public function searchForFacetValues($compositionID, $facetName, $searchForFacetValuesRequest = null, $requestOptions = [])
    {
        // verify the required parameter 'compositionID' is set
        if (!isset($compositionID)) {
            throw new \InvalidArgumentException(
                'Parameter `compositionID` is required when calling `searchForFacetValues`.'
            );
        }
        // verify the required parameter 'facetName' is set
        if (!isset($facetName)) {
            throw new \InvalidArgumentException(
                'Parameter `facetName` is required when calling `searchForFacetValues`.'
            );
        }

        $resourcePath = '/1/compositions/{compositionID}/facets/{facetName}/query';
        $queryParameters = [];
        $headers = [];
        $httpBody = isset($searchForFacetValuesRequest) ? $searchForFacetValuesRequest : [];

        // path params
        if (null !== $compositionID) {
            $resourcePath = str_replace(
                '{compositionID}',
                ObjectSerializer::toPathValue($compositionID),
                $resourcePath
            );
        }

        // path params
        if (null !== $facetName) {
            $resourcePath = str_replace(
                '{facetName}',
                ObjectSerializer::toPathValue($facetName),
                $resourcePath
            );
        }

        return $this->sendRequest('POST', $resourcePath, $headers, $queryParameters, $httpBody, $requestOptions, true);
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

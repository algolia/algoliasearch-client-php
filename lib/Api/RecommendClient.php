<?php

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\RecommendConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

/**
 * RecommendClient Class Doc Comment
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class RecommendClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var RecommendConfig
     */
    protected $config;

    /**
     * @param RecommendConfig $config
     * @param ApiWrapperInterface $apiWrapper
     */
    public function __construct(
        ApiWrapperInterface $apiWrapper,
        RecommendConfig $config
    ) {
        $this->config = $config;

        $this->api = $apiWrapper;
    }

    /**
     * Instantiate the client with basic credentials
     *
     * @param string $appId  Application ID
     * @param string $apiKey Algolia API Key
     */
    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(
            RecommendConfig::create($appId, $apiKey)
        );
    }

    /**
     * Instantiate the client with configuration
     *
     * @param RecommendConfig $config Configuration
     */
    public static function createWithConfig(RecommendConfig $config)
    {
        $config = clone $config;

        $cacheKey = sprintf(
            '%s-clusterHosts-%s',
            __CLASS__,
            $config->getAppId()
        );

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } elseif (
            false === ($clusterHosts = ClusterHosts::createFromCache($cacheKey))
        ) {
            // We'll try to restore the ClusterHost from cache, if we cannot
            // we create a new instance and set the cache key
            $clusterHosts = ClusterHosts::createFromAppId(
                $config->getAppId()
            )->setCacheKey($cacheKey);
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    /**
     * @return RecommendConfig
     */
    public function getClientConfig()
    {
        return $this->config;
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
     * Get results.
     *
     * @param array $getRecommendationsParams getRecommendationsParams (required)
     * - $getRecommendationsParams['requests'] => (array) The `getRecommendations` requests. (required)
     *
     * @see \Algolia\AlgoliaSearch\Model\Recommend\GetRecommendationsParams
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Recommend\GetRecommendationsResponse
     */
    public function getRecommendations(
        $getRecommendationsParams,
        $requestOptions = []
    ) {
        // verify the required parameter 'getRecommendationsParams' is set
        if (
            $getRecommendationsParams === null ||
            (is_array($getRecommendationsParams) &&
                count($getRecommendationsParams) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $getRecommendationsParams when calling getRecommendations'
            );
        }

        $resourcePath = '/1/indexes/*/recommendations';
        $queryParameters = [];
        $httpBody = [];

        if (isset($getRecommendationsParams)) {
            $httpBody = $getRecommendationsParams;
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

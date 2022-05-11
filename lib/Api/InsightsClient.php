<?php

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\InsightsConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

/**
 * InsightsClient Class Doc Comment
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class InsightsClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var InsightsConfig
     */
    protected $config;

    /**
     * @param InsightsConfig $config
     * @param ApiWrapperInterface $apiWrapper
     */
    public function __construct(
        ApiWrapperInterface $apiWrapper,
        InsightsConfig $config
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
        $allowedRegions = ['de', 'us'];
        $config = InsightsConfig::create(
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
     * @param InsightsConfig $config Configuration
     */
    public static function createWithConfig(InsightsConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $url = str_replace(
                '{region}',
                $config->getRegion(),
                'insights.{region}.algolia.io'
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
     * @return InsightsConfig
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
     * Push events.
     *
     * @param array $insightEvents insightEvents (required)
     * - $insightEvents['events'] => (array) Array of events sent. (required)
     *
     * @see \Algolia\AlgoliaSearch\Model\Insights\InsightEvents
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Insights\PushEventsResponse
     */
    public function pushEvents($insightEvents, $requestOptions = [])
    {
        // verify the required parameter 'insightEvents' is set
        if (
            $insightEvents === null ||
            (is_array($insightEvents) && count($insightEvents) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $insightEvents when calling pushEvents'
            );
        }

        $resourcePath = '/1/events';
        $queryParameters = [];
        $httpBody = [];

        if (isset($insightEvents)) {
            $httpBody = $insightEvents;
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

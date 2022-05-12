<?php

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\AbtestingConfig;
use Algolia\AlgoliaSearch\ObjectSerializer;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

/**
 * AbtestingClient Class Doc Comment
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class AbtestingClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var AbtestingConfig
     */
    protected $config;

    /**
     * @param AbtestingConfig $config
     * @param ApiWrapperInterface $apiWrapper
     */
    public function __construct(
        ApiWrapperInterface $apiWrapper,
        AbtestingConfig $config
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
        $config = AbtestingConfig::create(
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
     * @param AbtestingConfig $config Configuration
     */
    public static function createWithConfig(AbtestingConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $url = str_replace(
                '{region}',
                $config->getRegion(),
                'analytics.{region}.algolia.com'
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
     * @return AbtestingConfig
     */
    public function getClientConfig()
    {
        return $this->config;
    }

    /**
     * Create a test.
     *
     * @param array $addABTestsRequest addABTestsRequest (required)
     * - $addABTestsRequest['name'] => (string) A/B test name. (required)
     * - $addABTestsRequest['variant'] => (array) List of 2 variants for the A/B test. (required)
     * - $addABTestsRequest['endAt'] => (string) End date for the A/B test expressed as YYYY-MM-DDThh:mm:ssZ. (required)
     *
     * @see \Algolia\AlgoliaSearch\Model\Abtesting\AddABTestsRequest
     *
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Abtesting\ABTestResponse
     */
    public function addABTests($addABTestsRequest, $requestOptions = [])
    {
        // verify the required parameter 'addABTestsRequest' is set
        if (
            $addABTestsRequest === null ||
            (is_array($addABTestsRequest) && count($addABTestsRequest) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $addABTestsRequest when calling addABTests'
            );
        }

        $resourcePath = '/2/abtests';
        $queryParameters = [];
        $headers = [];
        $httpBody = [];

        if (isset($addABTestsRequest)) {
            $httpBody = $addABTestsRequest;
        }

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $headers,
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
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
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
        $headers = [];
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

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $headers,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Delete a test.
     *
     * @param int $id The A/B test ID. (required)
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Abtesting\ABTestResponse
     */
    public function deleteABTest($id, $requestOptions = [])
    {
        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling deleteABTest'
            );
        }

        $resourcePath = '/2/abtests/{id}';
        $queryParameters = [];
        $headers = [];
        $httpBody = [];

        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{id}',
                ObjectSerializer::toPathValue($id),
                $resourcePath
            );
        }

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $headers,
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
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
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
        $headers = [];
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

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $headers,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get a test.
     *
     * @param int $id The A/B test ID. (required)
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Abtesting\ABTest
     */
    public function getABTest($id, $requestOptions = [])
    {
        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling getABTest'
            );
        }

        $resourcePath = '/2/abtests/{id}';
        $queryParameters = [];
        $headers = [];
        $httpBody = [];

        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{id}',
                ObjectSerializer::toPathValue($id),
                $resourcePath
            );
        }

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $headers,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * List all tests.
     *
     * @param int $offset Position of the starting record. Used for paging. 0 is the first record. (optional, default to 0)
     * @param int $limit Number of records to return. Limit is the size of the page. (optional, default to 10)
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Abtesting\ListABTestsResponse
     */
    public function listABTests(
        $offset = null,
        $limit = null,
        $requestOptions = []
    ) {
        $resourcePath = '/2/abtests';
        $queryParameters = [];
        $headers = [];
        $httpBody = [];

        if ($offset !== null) {
            if (
                is_array($offset) &&
                !in_array(
                    'offset',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($offset as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters['offset'] = $offset;
            }
        }

        if ($limit !== null) {
            if (
                is_array($limit) &&
                !in_array(
                    'limit',
                    RequestOptionsFactory::getAttributesToFormat(),
                    true
                )
            ) {
                foreach ($limit as $key => $value) {
                    $queryParameters[$key] = $value;
                }
            } else {
                $queryParameters['limit'] = $limit;
            }
        }

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $headers,
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
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
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
        $headers = [];
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

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $headers,
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
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
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
        $headers = [];
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

        return $this->sendRequest(
            'PUT',
            $resourcePath,
            $headers,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Stop a test.
     *
     * @param int $id The A/B test ID. (required)
     * @param array $requestOptions the requestOptions to send along with the query, they will be merged with the transporter requestOptions
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Abtesting\ABTestResponse
     */
    public function stopABTest($id, $requestOptions = [])
    {
        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling stopABTest'
            );
        }

        $resourcePath = '/2/abtests/{id}/stop';
        $queryParameters = [];
        $headers = [];
        $httpBody = [];

        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{id}',
                ObjectSerializer::toPathValue($id),
                $resourcePath
            );
        }

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $headers,
            $queryParameters,
            $httpBody,
            $requestOptions
        );
    }

    private function sendRequest(
        $method,
        $resourcePath,
        $headers,
        $queryParameters,
        $httpBody,
        $requestOptions
    ) {
        if (!isset($requestOptions['headers'])) {
            $requestOptions['headers'] = [];
        }
        if (!isset($requestOptions['queryParameters'])) {
            $requestOptions['queryParameters'] = [];
        }

        $requestOptions['headers'] = array_merge(
            $headers,
            $requestOptions['headers']
        );
        $requestOptions['queryParameters'] = array_merge(
            $queryParameters,
            $requestOptions['queryParameters']
        );

        $query = \GuzzleHttp\Psr7\Query::build(
            $requestOptions['queryParameters']
        );

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

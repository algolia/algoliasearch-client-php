<?php

namespace Algolia\AlgoliaSearch\Api;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\PersonalizationConfig;
use Algolia\AlgoliaSearch\ObjectSerializer;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

/**
 * PersonalizationClient Class Doc Comment
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class PersonalizationClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var PersonalizationConfig
     */
    protected $config;

    /**
     * @param PersonalizationConfig $config
     * @param ApiWrapperInterface $apiWrapper
     */
    public function __construct(
        ApiWrapperInterface $apiWrapper,
        PersonalizationConfig $config
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
        $config = PersonalizationConfig::create(
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
     * @param PersonalizationConfig $config Configuration
     */
    public static function createWithConfig(PersonalizationConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $url = str_replace(
                '{region}',
                $config->getRegion(),
                'personalization.{region}.algolia.com'
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
     * @return PersonalizationConfig
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
        $queryParams = [];
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
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        $requestOptions += $queryParams;

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $queryParams,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Delete a user profile.
     *
     * @param string $userToken userToken representing the user for which to fetch the Personalization profile. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Personalization\DeleteUserProfileResponse
     */
    public function deleteUserProfile($userToken, $requestOptions = [])
    {
        // verify the required parameter 'userToken' is set
        if (
            $userToken === null ||
            (is_array($userToken) && count($userToken) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $userToken when calling deleteUserProfile'
            );
        }

        $resourcePath = '/1/profiles/{userToken}';
        $queryParams = [];
        $httpBody = [];

        // path params
        if ($userToken !== null) {
            $resourcePath = str_replace(
                '{userToken}',
                ObjectSerializer::toPathValue($userToken),
                $resourcePath
            );
        }

        $requestOptions += $queryParams;

        return $this->sendRequest(
            'DELETE',
            $resourcePath,
            $queryParams,
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
        $queryParams = [];
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
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        $requestOptions += $queryParams;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParams,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get the current strategy.
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Personalization\PersonalizationStrategyParams
     */
    public function getPersonalizationStrategy($requestOptions = [])
    {
        $resourcePath = '/1/strategies/personalization';
        $queryParams = [];
        $httpBody = [];

        $requestOptions += $queryParams;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParams,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Get a user profile.
     *
     * @param string $userToken userToken representing the user for which to fetch the Personalization profile. (required)
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Personalization\GetUserTokenResponse
     */
    public function getUserTokenProfile($userToken, $requestOptions = [])
    {
        // verify the required parameter 'userToken' is set
        if (
            $userToken === null ||
            (is_array($userToken) && count($userToken) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $userToken when calling getUserTokenProfile'
            );
        }

        $resourcePath = '/1/profiles/personalization/{userToken}';
        $queryParams = [];
        $httpBody = [];

        // path params
        if ($userToken !== null) {
            $resourcePath = str_replace(
                '{userToken}',
                ObjectSerializer::toPathValue($userToken),
                $resourcePath
            );
        }

        $requestOptions += $queryParams;

        return $this->sendRequest(
            'GET',
            $resourcePath,
            $queryParams,
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
        $queryParams = [];
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
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        if (isset($body)) {
            $httpBody = $body;
        }
        $requestOptions += $queryParams;

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $queryParams,
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
        $queryParams = [];
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
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams = $parameters;
            }
        }

        // path params
        if ($path !== null) {
            $resourcePath = str_replace('{path}', $path, $resourcePath);
        }

        if (isset($body)) {
            $httpBody = $body;
        }
        $requestOptions += $queryParams;

        return $this->sendRequest(
            'PUT',
            $resourcePath,
            $queryParams,
            $httpBody,
            $requestOptions
        );
    }

    /**
     * Set a new strategy.
     *
     * @param array $personalizationStrategyParams personalizationStrategyParams (required)
     * - $personalizationStrategyParams['eventScoring'] => (array) Scores associated with the events. (required)
     * - $personalizationStrategyParams['facetScoring'] => (array) Scores associated with the facets. (required)
     * - $personalizationStrategyParams['personalizationImpact'] => (int) The impact that personalization has on search results: a number between 0 (personalization disabled) and 100 (personalization fully enabled). (required)
     *
     * @see \Algolia\AlgoliaSearch\Model\Personalization\PersonalizationStrategyParams
     *
     * @param array $requestOptions Request Options
     *
     * @return array<string, mixed>|\Algolia\AlgoliaSearch\Model\Personalization\SetPersonalizationStrategyResponse
     */
    public function setPersonalizationStrategy(
        $personalizationStrategyParams,
        $requestOptions = []
    ) {
        // verify the required parameter 'personalizationStrategyParams' is set
        if (
            $personalizationStrategyParams === null ||
            (is_array($personalizationStrategyParams) &&
                count($personalizationStrategyParams) === 0)
        ) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $personalizationStrategyParams when calling setPersonalizationStrategy'
            );
        }

        $resourcePath = '/1/strategies/personalization';
        $queryParams = [];
        $httpBody = [];

        if (isset($personalizationStrategyParams)) {
            $httpBody = $personalizationStrategyParams;
        }
        $requestOptions += $queryParams;

        return $this->sendRequest(
            'POST',
            $resourcePath,
            $queryParams,
            $httpBody,
            $requestOptions
        );
    }

    private function sendRequest(
        $method,
        $resourcePath,
        $queryParams,
        $httpBody,
        $requestOptions
    ) {
        $query = \GuzzleHttp\Psr7\Query::build($queryParams);

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

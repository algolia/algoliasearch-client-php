<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\ValidUntilNotFoundException;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\Response\AddApiKeyResponse;
use Algolia\AlgoliaSearch\Response\DeleteApiKeyResponse;
use Algolia\AlgoliaSearch\Response\IndexingResponse;
use Algolia\AlgoliaSearch\Response\MultipleIndexBatchIndexingResponse;
use Algolia\AlgoliaSearch\Response\RestoreApiKeyResponse;
use Algolia\AlgoliaSearch\Response\UpdateApiKeyResponse;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Support\Helpers;

class SearchClient
{
    /**
     * @var ApiWrapperInterface
     */
    protected $api;

    /**
     * @var SearchConfig
     */
    protected $config;

    protected static $client;

    public function __construct(ApiWrapperInterface $apiWrapper, SearchConfig $config)
    {
        $this->api = $apiWrapper;
        $this->config = $config;
    }

    public static function get()
    {
        if (!static::$client) {
            static::$client = static::create();
        }

        return static::$client;
    }

    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(SearchConfig::create($appId, $apiKey));
    }

    public static function createWithConfig(SearchConfig $config)
    {
        $config = clone $config;

        $cacheKey = sprintf('%s-clusterHosts-%s', __CLASS__, $config->getAppId());

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } elseif (false === ($clusterHosts = ClusterHosts::createFromCache($cacheKey))) {
            // We'll try to restore the ClusterHost from cache, if we cannot
            // we create a new instance and set the cache key
            $clusterHosts = ClusterHosts::createFromAppId($config->getAppId())
                ->setCacheKey($cacheKey);
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function initIndex($indexName)
    {
        return new SearchIndex($indexName, $this->api, $this->config);
    }

    public function getAppId()
    {
        return $this->config->getAppId();
    }

    public function moveIndex($srcIndexName, $newIndexName, $requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'move',
                'destination' => $newIndexName,
            ),
            $requestOptions
        );

        return new IndexingResponse($response, $this->initIndex($srcIndexName));
    }

    public function copyIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'copy',
                'destination' => $destIndexName,
            ),
            $requestOptions
        );

        return new IndexingResponse($response, $this->initIndex($srcIndexName));
    }

    public function copySettings($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('settings');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('settings'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    public function copySynonyms($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('synonyms');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('synonyms'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    public function copyRules($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('rules');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('rules'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    public function isAlive($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/isalive'), $requestOptions);
    }

    public function multipleQueries($queries, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['requests'] = $queries;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('requests', $queries);
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/*/queries'),
            $requestOptions
        );
    }

    public function multipleBatch($operations, $requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/*/batch'),
            array('requests' => $operations),
            $requestOptions
        );

        return new MultipleIndexBatchIndexingResponse($response, $this);
    }

    public function multipleGetObjects($requests, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['requests'] = $requests;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('requests', $requests);
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/*/objects'),
            $requestOptions
        );
    }

    public function listIndices($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
    }

    public function listApiKeys($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys'), $requestOptions);
    }

    public function getApiKey($key, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys/%s', $key), $requestOptions);
    }

    public function addApiKey($acl, $requestOptions = array())
    {
        $acl = array('acl' => $acl);

        $response = $this->api->write('POST', api_path('/1/keys'), $acl, $requestOptions);

        return new AddApiKeyResponse($response, $this, $this->config);
    }

    public function updateApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('PUT', api_path('/1/keys/%s', $key), array(), $requestOptions);

        return new UpdateApiKeyResponse($response, $this, $this->config, $requestOptions);
    }

    public function deleteApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('DELETE', api_path('/1/keys/%s', $key), array(), $requestOptions);

        return new DeleteApiKeyResponse($response, $this, $this->config, $key);
    }

    public function restoreApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('POST', api_path('/1/keys/%s/restore', $key), array(), $requestOptions);

        return new RestoreApiKeyResponse($response, $this, $this->config, $key);
    }

    public static function generateSecuredApiKey($parentApiKey, $restrictions)
    {
        $urlEncodedRestrictions = Helpers::buildQuery($restrictions);

        $content = hash_hmac('sha256', $urlEncodedRestrictions, $parentApiKey).$urlEncodedRestrictions;

        return base64_encode($content);
    }

    /**
     * @deprecated endpoint will be deprecated
     * @see RecommendationClient
     */
    public function getPersonalizationStrategy($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/recommendation/personalization/strategy'), $requestOptions);
    }

    /**
     * @deprecated endpoint will be deprecated
     * @see RecommendationClient
     */
    public function setPersonalizationStrategy($strategy, $requestOptions = array())
    {
        $apiResponse = $this->api->write(
            'POST',
            api_path('1/recommendation/personalization/strategy'),
            $strategy,
            $requestOptions
        );

        return $apiResponse;
    }

    public function searchUserIds($query, $requestOptions = array())
    {
        $query = (string) $query;

        if (is_array($requestOptions)) {
            $requestOptions['query'] = $query;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('query', $query);
        }

        return $this->api->read('POST', api_path('/1/clusters/mapping/search'), $requestOptions);
    }

    public function listClusters($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters'), $requestOptions);
    }

    public function listUserIds($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping'), $requestOptions);
    }

    public function getUserId($userId, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping/%s', $userId), $requestOptions);
    }

    /**
     * @deprecated since 2.6.1, use getTopUserIds instead.
     */
    public function getTopUserId($requestOptions = array())
    {
        return $this->getTopUserIds($requestOptions);
    }

    /**
     * Get the top 10 userIDs with the highest number of records per cluster.
     *
     * @param array $requestOptions
     *
     * @return array<string, mixed>
     */
    public function getTopUserIds($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping/top'), $requestOptions);
    }

    public function assignUserId($userId, $clusterName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['X-Algolia-User-ID'] = $userId;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addHeader('X-Algolia-User-ID', $userId);
        }

        return $this->api->write(
            'POST',
            api_path('/1/clusters/mapping'),
            array(
                'cluster' => $clusterName,
            ),
            $requestOptions
        );
    }

    /**
     * Assign multiple userIds to the given cluster name.
     *
     * @param array<int, int> $userIds
     * @param string          $clusterName
     * @param array           $requestOptions
     *
     * @return array<string, mixed>
     */
    public function assignUserIds($userIds, $clusterName, $requestOptions = array())
    {
        return $this->api->write(
             'POST',
             api_path('/1/clusters/mapping/batch'),
             array(
                 'users' => $userIds,
                 'cluster' => $clusterName,
             ),
             $requestOptions
         );
    }

    public function removeUserId($userId, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['X-Algolia-User-ID'] = $userId;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addHeader('X-Algolia-User-ID', $userId);
        }

        return $this->api->write(
            'DELETE',
            api_path('/1/clusters/mapping'),
            array(),
            $requestOptions
        );
    }

    public function getLogs($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/logs'), $requestOptions);
    }

    public function getTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->initIndex($indexName);

        return $index->getTask($taskId, $requestOptions);
    }

    public function waitTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->initIndex($indexName);

        $index->waitTask($taskId, $requestOptions);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }

    /**
     * Returns the time the given securedAPIKey remains valid in seconds.
     *
     * @param string $securedAPIKey the key to check
     *
     * @return int remaining validity in seconds
     *
     * @throws ValidUntilNotFoundException
     */
    public static function getSecuredApiKeyRemainingValidity($securedAPIKey)
    {
        $decodedKey = base64_decode($securedAPIKey);
        $regex = '/validUntil=(\d+)/';
        preg_match($regex, $decodedKey, $matches);

        if (0 === count($matches)) {
            throw new ValidUntilNotFoundException("The SecuredAPIKey doesn't have a validUntil parameter.");
        }

        $validUntil = (int) $matches[1];

        return $validUntil - time();
    }

    /**
     * Get cluster pending (migrating, creating, deleting) mapping state. Query cluster pending mapping status and get cluster mappings.
     *
     * @param array<string, mixed> $requestOptions
     *
     * @return array<string, boolean|array>
     */
    public function hasPendingMappings($requestOptions = array())
    {
        if (isset($requestOptions['retrieveMappings'])
            && true === $requestOptions['retrieveMappings']) {
            if (is_array($requestOptions)) {
                $requestOptions['getClusters'] = true;
            } elseif ($requestOptions instanceof RequestOptions) {
                $requestOptions->addQueryParameter('getClusters', true);
            }
        }

        return $this->api->read(
            'GET',
            api_path('/1/clusters/mapping/pending'),
            $requestOptions
        );
    }
}

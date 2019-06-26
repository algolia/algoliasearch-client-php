<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Response\DeleteApiKeyResponse;
use Algolia\AlgoliaSearch\Response\IndexingResponse;
use Algolia\AlgoliaSearch\Response\MultipleIndexBatchIndexingResponse;
use Algolia\AlgoliaSearch\Response\AddApiKeyResponse;
use Algolia\AlgoliaSearch\Response\RestoreApiKeyResponse;
use Algolia\AlgoliaSearch\Response\UpdateApiKeyResponse;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Support\Helpers;

final class SearchClient
{
    /**
     * @var ApiWrapperInterface
     */
    private $api;

    /**
     * @var SearchConfig
     */
    private $config;

    /**
     * @var SearchClient
     */
    private static $client;

    /**
     * SearchClient constructor.
     *
     * @param ApiWrapperInterface $apiWrapper
     * @param SearchConfig        $config
     */
    public function __construct(ApiWrapperInterface $apiWrapper, SearchConfig $config)
    {
        $this->api = $apiWrapper;
        $this->config = $config;
    }

    /**
     * @return SearchClient
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get()
    {
        if (!static::$client) {
            static::$client = static::create();
        }

        return static::$client;
    }

    /**
     * @param string|null $appId
     * @param string|null $apiKey
     *
     * @return SearchClient
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(SearchConfig::create($appId, $apiKey));
    }

    /**
     * @param SearchConfig $config
     *
     * @return SearchClient
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
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

    /**
     * @param string $indexName
     *
     * @return SearchIndex
     */
    public function initIndex($indexName)
    {
        return new SearchIndex($indexName, $this->api, $this->config);
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->config->getAppId();
    }

    /**
     * @param string $srcIndexName
     * @param string $newIndexName
     * @param array  $requestOptions
     *
     * @return IndexingResponse
     */
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

    /**
     * @param string $srcIndexName
     * @param string $destIndexName
     * @param array  $requestOptions
     *
     * @return IndexingResponse
     */
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

    /**
     * @param string $srcIndexName
     * @param string $destIndexName
     * @param array  $requestOptions
     *
     * @return IndexingResponse
     */
    public function copySettings($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('settings');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('settings'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    /**
     * @param string $srcIndexName
     * @param string $destIndexName
     * @param array  $requestOptions
     *
     * @return IndexingResponse
     */
    public function copySynonyms($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('synonyms');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('synonyms'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    /**
     * @param string $srcIndexName
     * @param string $destIndexName
     * @param array  $requestOptions
     *
     * @return IndexingResponse
     */
    public function copyRules($srcIndexName, $destIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('rules');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('rules'));
        }

        return $this->copyIndex($srcIndexName, $destIndexName, $requestOptions);
    }

    /**
     * @param array $requestOptions
     *
     * @return array
     */
    public function isAlive($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/isalive'), $requestOptions);
    }

    /**
     * @param array $queries
     * @param array $requestOptions
     *
     * @return array
     */
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

    /**
     * @param array $operations
     * @param array $requestOptions
     *
     * @return MultipleIndexBatchIndexingResponse
     */
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

    /**
     * @param array $requests
     * @param array $requestOptions
     *
     * @return mixed
     */
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

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function listIndices($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
    }

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function listApiKeys($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys'), $requestOptions);
    }

    /**
     * @param string $key
     * @param array  $requestOptions
     *
     * @return mixed
     */
    public function getApiKey($key, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys/%s', $key), $requestOptions);
    }

    /**
     * @param array $acl
     * @param array $requestOptions
     *
     * @return AddApiKeyResponse
     */
    public function addApiKey($acl, $requestOptions = array())
    {
        $acl = array('acl' => $acl);

        $response = $this->api->write('POST', api_path('/1/keys'), $acl, $requestOptions);

        return new AddApiKeyResponse($response, $this, $this->config);
    }

    /**
     * @param string $key
     * @param array  $requestOptions
     *
     * @return UpdateApiKeyResponse
     */
    public function updateApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('PUT', api_path('/1/keys/%s', $key), array(), $requestOptions);

        return new UpdateApiKeyResponse($response, $this, $this->config, $requestOptions);
    }

    /**
     * @param string $key
     * @param array  $requestOptions
     *
     * @return DeleteApiKeyResponse
     */
    public function deleteApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('DELETE', api_path('/1/keys/%s', $key), array(), $requestOptions);

        return new DeleteApiKeyResponse($response, $this, $this->config, $key);
    }

    /**
     * @param string $key
     * @param array  $requestOptions
     *
     * @return RestoreApiKeyResponse
     */
    public function restoreApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write('POST', api_path('/1/keys/%s/restore', $key), array(), $requestOptions);

        return new RestoreApiKeyResponse($response, $this, $this->config, $key);
    }

    /**
     * @param string $parentApiKey
     * @param mixed  $restrictions
     *
     * @return string
     */
    public static function generateSecuredApiKey($parentApiKey, $restrictions)
    {
        $urlEncodedRestrictions = Helpers::buildQuery($restrictions);

        $content = hash_hmac('sha256', $urlEncodedRestrictions, $parentApiKey).$urlEncodedRestrictions;

        return base64_encode($content);
    }

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function getPersonalizationStrategy($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/recommendation/personalization/strategy'), $requestOptions);
    }

    /**
     * @param mixed $strategy
     * @param array $requestOptions
     *
     * @return mixed
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

    /**
     * @param mixed $query
     * @param array $requestOptions
     *
     * @return mixed
     */
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

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function listClusters($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters'), $requestOptions);
    }

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function listUserIds($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping'), $requestOptions);
    }

    /**
     * @param mixed $userId
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function getUserId($userId, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping/%s', $userId), $requestOptions);
    }

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function getTopUserId($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping/%top'), $requestOptions);
    }

    /**
     * @param mixed  $userId
     * @param string $clusterName
     * @param array  $requestOptions
     *
     * @return mixed
     */
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
     * @param mixed $userId
     * @param array $requestOptions
     *
     * @return mixed
     */
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

    /**
     * @param array $requestOptions
     *
     * @return mixed
     */
    public function getLogs($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/logs'), $requestOptions);
    }

    /**
     * @param string $indexName
     * @param int    $taskId
     * @param array  $requestOptions
     *
     * @return mixed
     */
    public function getTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->initIndex($indexName);

        return $index->getTask($taskId, $requestOptions);
    }

    /**
     * @param string $indexName
     * @param int    $taskId
     * @param array  $requestOptions
     *
     * @return void
     */
    public function waitTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->initIndex($indexName);

        $index->waitTask($taskId, $requestOptions);
    }

    /**
     * @param string     $method
     * @param string     $path
     * @param array      $requestOptions
     * @param array|null $hosts
     *
     * @return mixed
     */
    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

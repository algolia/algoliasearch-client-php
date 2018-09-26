<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Interfaces\ConfigInterface;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Config\ClientConfig;
use Algolia\AlgoliaSearch\Support\Helpers;

class Client implements ClientInterface
{
    /**
     * @var ApiWrapper
     */
    protected $api;

    /**
     * @var ConfigInterface
     */
    protected $config;

    protected static $client;

    public function __construct(ApiWrapper $apiWrapper, ClientConfig $config)
    {
        $this->api = $apiWrapper;
        $this->config = $config;
    }

    public static function get($appId = null, $apiKey = null)
    {
        if (!static::$client) {
            static::$client = static::create($appId, $apiKey);
        }

        return static::$client;
    }

    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(ClientConfig::create($appId, $apiKey));
    }

    public static function createWithConfig(ClientConfig $config)
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
            HttpClientFactory::get(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function initIndex($indexName)
    {
        return new Index($indexName, $this->api, $this->config);
    }

    public function setExtraHeader($headerName, $headerValue)
    {
        $this->api->setExtraHeader($headerName, $headerValue);

        return $this;
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

    public function multipleBatchObjects($operations, $requestOptions = array())
    {
        Helpers::ensureObjectID($operations);

        if (is_array($requestOptions)) {
            $requestOptions['requests'] = $operations;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('requests', $operations);
        }

        return $this->api->write(
            'POST',
            api_path('/1/indexes/*/batch'),
            $requestOptions
        );
    }

    public function multipleGetObjects($requests, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['requests'] = $requests;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('requests', $requests);
        }

        return $this->api->write(
            'POST',
            api_path('/1/indexes/*/objects'),
            $requestOptions
        );
    }

    public function listIndexes($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
    }

    public function moveIndex($srcIndexName, $dstIndexName, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'move',
                'destination' => $dstIndexName,
            ),
            $requestOptions
        );
    }

    public function copyIndex($srcIndexName, $dstIndexName, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'copy',
                'destination' => $dstIndexName,
            ),
            $requestOptions
        );
    }

    public function clearIndex($indexName, $requestOptions = array())
    {
        return $this->initIndex($indexName)->clear($requestOptions);
    }

    public function deleteIndex($indexName, $requestOptions = array())
    {
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s', $indexName),
            array(),
            $requestOptions
        );
    }

    public function copySettings($srcIndexName, $dstIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('settings');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('settings'));
        }

        return $this->copyIndex($srcIndexName, $dstIndexName, $requestOptions);
    }

    public function copySynonyms($srcIndexName, $dstIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('synonyms');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('synonyms'));
        }

        return $this->copyIndex($srcIndexName, $dstIndexName, $requestOptions);
    }

    public function copyRules($srcIndexName, $dstIndexName, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['scope'] = array('rules');
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('scope', array('rules'));
        }

        return $this->copyIndex($srcIndexName, $dstIndexName, $requestOptions);
    }

    public function listApiKeys($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys'), $requestOptions);
    }

    public function getApiKey($key, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys/%s', $key), $requestOptions);
    }

    public function addApiKey($keyParams, $requestOptions = array())
    {
        return $this->api->write('POST', api_path('/1/keys'), $keyParams, $requestOptions);
    }

    public function updateApiKey($key, $keyParams, $requestOptions = array())
    {
        return $this->api->write('PUT', api_path('/1/keys/%s', $key), $keyParams, $requestOptions);
    }

    public function deleteApiKey($key, $requestOptions = array())
    {
        return $this->api->write('DELETE', api_path('/1/keys/%s', $key), array(), $requestOptions);
    }

    // BC Break: signature was changed
    public static function generateSecuredApiKey($parentApiKey, $restrictions)
    {
        $urlEncodedRestrictions = Helpers::buildQuery($restrictions);

        $content = hash_hmac('sha256', $urlEncodedRestrictions, $parentApiKey).$urlEncodedRestrictions;

        return base64_encode($content);
    }

    public function searchUserIds($query, $requestOptions = array())
    {
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

    public function getTopUserId($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/clusters/mapping/%top'), $requestOptions);
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

        return $index->waitTask($taskId, $requestOptions);
    }

    public function waitForKeyAdded($key, $requestOptions = array())
    {
        $retry = 1;
        $maxRetry = $this->config->getWaitTaskMaxRetry();
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            try {
                $this->getApiKey($key, $requestOptions);

                return;
            } catch (NotFoundException $e) {
                // Try again
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException('The key '.substr($key, 0, 6)."... isn't added yet.");
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

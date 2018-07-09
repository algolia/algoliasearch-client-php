<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Http\Guzzle6HttpClient;
use Algolia\AlgoliaSearch\Interfaces\Client as ClientInterface;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\Internals\RequestOptions;
use Algolia\AlgoliaSearch\Internals\RequestOptionsFactory;
use GuzzleHttp\Client as GuzzleClient;

final class Client implements ClientInterface
{
    /**
     * @var ApiWrapper
     */
    private $api;

    public function __construct(ApiWrapper $apiWrapper)
    {
        $this->api = $apiWrapper;
    }

    public static function create($appId, $apiKey, $hosts = null)
    {
        if (!$hosts) {
            $hosts = ClusterHosts::createFromAppId($appId);
        } elseif (is_string($hosts)) {
            $hosts = new ClusterHosts(array($hosts));
        } elseif (is_array($hosts)) {
            $hosts = new ClusterHosts($hosts);
        }

        $apiWrapper = new ApiWrapper(
            $hosts,
            new RequestOptionsFactory($appId, $apiKey),
            new Guzzle6HttpClient(new GuzzleClient())
        );

        return new static($apiWrapper);
    }

    public function index($indexName)
    {
        return new Index($indexName, $this->api);
    }

    public function listIndexes($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
    }

    public function moveIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'move',
                'destination' => $destIndexName,
            ),
            $requestOptions
        );
    }

    // BC Break: ScopedCopyIndex was removed
    public function copyIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            array(
                'operation' => 'copy',
                'destination' => $destIndexName,
            ),
            $requestOptions
        );
    }

    public function clearIndex($indexName, $requestOptions = array())
    {
        return $this->index($indexName)->clear($requestOptions);
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
        $urlEncodedRestrictions = Helpers::build_query($restrictions);

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
        return $this->api->read('GET', api_path('/1/clusters/mapping'), $requestOptions, array(
            'page' => 0,
            'hitsPerPage' => 20,
        ));
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
        return $this->api->read('GET', api_path('/1/logs'), $requestOptions, array(
            'offset' => 0,
            'length' => 10,
            'type' => 'all',
        ));
    }

    public function getTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->index($indexName);
        return $index->getTask($taskId, $requestOptions);
    }

    public function waitTask($indexName, $taskId, $requestOptions = array())
    {
        $index = $this->index($indexName);
        return $index->waitTask($taskId, $requestOptions);
    }
}

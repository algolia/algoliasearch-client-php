<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Http\Guzzle6HttpClient;
use Algolia\AlgoliaSearch\Interfaces\Client as ClientInterface;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
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
        if (! $hosts) {
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

    /**
     * @see https://alg.li/list-indexes-php
     * @Api
     */
    public function listIndexes($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
    }

    /**
     * @see https://alg.li/copy-index-php
     * @Api
     */
    public function copyIndex($srcIndexName, $destIndexName, $scope = array(), $requestOptions = array())
    {
        $requestOptions += array(
            'operation' => 'copy',
            'destination' => $destIndexName,
            'scope' => $scope,
        );

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            $requestOptions
        );
    }

    public function moveIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        $requestOptions += array(
            'operation' => 'move',
            'destination' => $destIndexName,
        );

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
            $requestOptions
        );
    }

    public function deleteIndex($indexName, $requestOptions = array())
    {
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s', $indexName),
            $requestOptions
        );
    }

    public function listApiKeys($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys'), $requestOptions);
    }

    /**
     * @see https://alg.li/get-api-key-php
     * @Api
     */
    public function getApiKey($key, $requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/keys/%s', $key), $requestOptions);
    }

    /**
     * @see https://alg.li/add-api-key-php
     */
    public function addApiKey($keyDetails, $requestOptions = array())
    {
        $requestOptions += $keyDetails;

        return $this->api->write('POST', api_path('/1/keys'), $requestOptions);
    }

    /**
     * @see https://alg.li/delete-api-key-php
     */
    public function deleteApiKey($key, $requestOptions = array())
    {
        return $this->api->write('DELETE', api_path('/1/keys/%s', $key), $requestOptions);
    }
}

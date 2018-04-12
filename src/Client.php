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
        if (is_null($hosts)) {
            $hosts = ClusterHosts::createFromAppId($appId);
        } elseif (is_string($hosts)) {
            $hosts = new ClusterHosts([$hosts]);
        } elseif (is_array($hosts)) {
            $hosts = new ClusterHosts($hosts);
        }

        $apiWrapper = new ApiWrapper(
            $hosts,
            new RequestOptionsFactory($appId, $apiKey),
            new Guzzle6HttpClient(new GuzzleClient)
        );

        return new static($apiWrapper);
    }

    public function index($indexName)
    {
        return new Index($indexName, $this->api);
    }

    /**
     * @link https://alg.li/list-indices-php
     * @Api
     */
    public function listIndices($page = 0, $requestOptions = [])
    {
        $requestOptions = array_merge(
            compact('page'),
            $requestOptions
        );

        return $this->api->read('GET', '/1/indexes/', $requestOptions);
    }

    /**
     * @link https://alg.li/copy-index-php
     * @Api
     */
    public function copyIndex($srcIndexName, $dstIndexName, $scope = [], $requestOptions = [])
    {
        $requestOptions += [
            'operation' => 'copy',
            'destination' => $dstIndexName,
            'scope' => $scope
        ];

        return $this->api->write(
            'POST',
            '/1/indexes/'.urlencode($srcIndexName).'/operation',
            $requestOptions
        );
    }

    /**
     * @link https://alg.li/get-api-key-php
     * @Api
     */
    public function getApiKey($key, $requestOptions = [])
    {
        return $this->api->read('GET', '/1/keys/'.urlencode($key), $requestOptions);
    }

    /**
     * @link https://alg.li/delete-api-key-php
     */
    public function deleteApiKey($key, $requestOptions = [])
    {
        return $this->api->write('DELETE', '/1/keys/'.urlencode($key), $requestOptions);
    }

    /**
     * @link https://alg.li/add-api-key-php
     */
    public function addApiKey($keyDetails, $requestOptions = [])
    {
        $requestOptions += $keyDetails;

        return $this->api->write('POST', '/1/keys', $requestOptions);
    }
}

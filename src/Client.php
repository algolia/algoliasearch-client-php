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

    public function listIndexes($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/indexes/'), $requestOptions);
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

    // BC Break: ScopedCopyIndex was removed
    public function copyIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        $requestOptions += array(
            'operation' => 'copy',
            'destination' => $destIndexName,
        );

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $srcIndexName),
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
        $requestOptions += $keyParams;

        return $this->api->write('POST', api_path('/1/keys'), $requestOptions);
    }

    public function updateApiKey($key, $keyParams, $requestOptions = array())
    {
        $requestOptions += $keyParams;

        return $this->api->write('PUT', api_path('/1/keys/%s', $key), $requestOptions);
    }

    public function deleteApiKey($key, $requestOptions = array())
    {
        return $this->api->write('DELETE', api_path('/1/keys/%s', $key), $requestOptions);
    }

    // BC Break: signature was changed
    public static function generateSecuredApiKey($parentApiKey, $restrictions)
    {
        $urlEncodedRestrictions = build_query($restrictions);

        $content = hash_hmac('sha256', $urlEncodedRestrictions, $parentApiKey).$urlEncodedRestrictions;

        return base64_encode($content);
    }

    public function getLogs($requestOptions = array(
        'offset' => 0,
        'length' => 10,
        'type' => 'all',
    ))
    {
        return $this->api->read('GET', api_path('/1/logs'), $requestOptions);
    }
}

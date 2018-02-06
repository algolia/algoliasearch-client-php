<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Http\Guzzle6HttpClient;
use Algolia\AlgoliaSearch\Contracts\ClientInterface;
use Algolia\AlgoliaSearch\Http\Lol;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\Internals\RequestOptionsFactory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;

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

    public function listIndices($page = 0, $requestOptions = [])
    {
        $requestOptions = array_merge(
            compact('page'),
            $requestOptions
        );

        return $this->api->read('GET', '/1/indexes/', $requestOptions);
    }

    public function index($indexName)
    {
        return new Index($indexName, $this->api);
    }
}

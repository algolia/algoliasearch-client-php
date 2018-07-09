<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Http\Guzzle6HttpClient;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use GuzzleHttp\Client as GuzzleClient;

final class Places
{
    /**
     * @var ApiWrapper
     */
    private $apiWrapper;

    public function __construct(ApiWrapper $apiWrapper)
    {
        $this->apiWrapper = $apiWrapper;
    }

    public static function create($appId = null, $apiKey = null, $hosts = null)
    {
        if (is_null($hosts)) {
            $hosts = ClusterHosts::createForPlaces();
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
}

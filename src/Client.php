<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Contracts\ClientInterface;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;

class Client implements ClientInterface
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
            $appId,
            $apiKey,
            $hosts,
            new \Http\Adapter\Guzzle6\Client(),
            new GuzzleMessageFactory(),
            new GuzzleUriFactory()
        );

        return new static($apiWrapper);
    }

    public function listIndices($requestOptions = [])
    {
        return $this->api->get('/1/indexes/', $requestOptions);
    }
}

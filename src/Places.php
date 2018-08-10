<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\Config;

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

    public static function create($appId = null, $apiKey = null)
    {
        $config = new ClientConfig(array(
            'appId' => $appId,
            'apiKey' => $apiKey,
        ));

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ClientConfig $config)
    {
        $apiWrapper = new ApiWrapper(
            Config::getHttpClient(),
            $config,
            ClusterHosts::createForPlaces()
        );

        return new static($apiWrapper);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

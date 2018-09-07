<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\HttpLayer;

final class Places
{
    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var ClientConfig
     */
    private $config;

    public function __construct(ApiWrapper $api, ClientConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = ClientConfig::create($appId, $apiKey);
        $config->setHosts(ClusterHosts::createForPlaces());

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ClientConfig $config)
    {
        $apiWrapper = new ApiWrapper(
            HttpLayer::get(),
            $config
        );

        return new static($apiWrapper, $config);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

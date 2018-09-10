<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\HttpLayer;

final class Places
{
    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var ClientConfigInterface
     */
    private $config;

    public function __construct(ApiWrapper $api, ClientConfigInterface $config)
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

    public static function createWithConfig(ClientConfigInterface $config)
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

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
    private $api;

    public function __construct(ApiWrapper $apiWrapper)
    {
        $this->api = $apiWrapper;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = new ClientConfig($appId, $apiKey);
        $config->setHosts(ClusterHosts::createForPlaces());

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ClientConfig $config)
    {
        $apiWrapper = new ApiWrapper(
            Config::getHttpClient(),
            $config
        );

        return new static($apiWrapper);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

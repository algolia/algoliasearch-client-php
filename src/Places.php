<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\SimpleConfig;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Interfaces\ConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class Places
{
    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ApiWrapper $api, ConfigInterface $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = SimpleConfig::create($appId, $apiKey);

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ConfigInterface $config)
    {
        $config = clone $config;

        $cacheKey = sprintf('%s-clusterHosts-%s', __CLASS__, $config->getAppId());

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } elseif (false !== ($clusterHosts = ClusterHosts::createFromCache($cacheKey))) {
            // We'll try to restore the ClusterHost from cache, if we cannot
            // we create a new instance and set the cache key
            $clusterHosts = ClusterHosts::createFromAppId($config->getAppId())
                ->setCacheKey($cacheKey);
        }

        $apiWrapper = new ApiWrapper(
            HttpClientFactory::get(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\PlacesConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class PlacesClient
{
    /**
     * @var ApiWrapperInterface
     */
    private $api;

    /**
     * @var PlacesConfig
     */
    private $config;

    public function __construct(ApiWrapperInterface $api, PlacesConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = PlacesConfig::create($appId, $apiKey);

        return static::createWithConfig($config);
    }

    public static function createWithConfig(PlacesConfig $config)
    {
        $config = clone $config;

        $cacheKey = sprintf('%s-clusterHosts-%s', __CLASS__, $config->getAppId());

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } elseif (false === ($clusterHosts = ClusterHosts::createFromCache($cacheKey))) {
            // We'll try to restore the ClusterHost from cache, if we cannot
            // we create a new instance and set the cache key
            $clusterHosts = ClusterHosts::createForPlaces()
                ->setCacheKey($cacheKey);
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function search($query, $requestOptions = [])
    {
        $query = (string) $query;

        if (is_array($requestOptions)) {
            $requestOptions['query'] = $query;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('query', $query);
        }

        return $this->api->read('POST', api_path('/1/places/query'), $requestOptions);
    }

    public function getObject($objectID, $requestOptions = [])
    {
        return $this->api->read('GET', api_path('/1/places/%s', $objectID), $requestOptions);
    }

    public function custom($method, $path, $requestOptions = [], $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}

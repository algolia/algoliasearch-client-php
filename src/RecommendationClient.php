<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\RecommendationConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class RecommendationClient
{
    /**
     * @var \Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper
     */
    private $api;

    /**
     * @var \Algolia\AlgoliaSearch\Config\RecommendationConfig
     */
    private $config;

    /**
     * RecommendationClient constructor.
     */
    public function __construct(ApiWrapper $api, RecommendationConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    /**
     * @param string|null $appId
     * @param string|null $apiKey
     * @param string|null $region
     *
     * @return RecommendationClient
     */
    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $config = RecommendationConfig::create($appId, $apiKey, $region);

        return static::createWithConfig($config);
    }

    /**
     * @return RecommendationClient
     */
    public static function createWithConfig(RecommendationConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForRecommendation($config->getRegion());
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new self($apiWrapper, $config);
    }

    /**
     * @param array<string, int|string|array>|RequestOptions $requestOptions
     *
     * @return array<string, int|array>
     */
    public function getPersonalizationStrategy($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/1/strategies/personalization'), $requestOptions);
    }

    /**
     * @param array<string, int|array>                       $strategy
     * @param array<string, int|string|array>|RequestOptions $requestOptions
     *
     * @return array<string, int|string>
     */
    public function setPersonalizationStrategy($strategy, $requestOptions = array())
    {
        return $this->api->write('POST', api_path('/1/strategies/personalization'), $strategy, $requestOptions);
    }
}

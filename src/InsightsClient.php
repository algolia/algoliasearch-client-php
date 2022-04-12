<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\InsightsConfig;
use Algolia\AlgoliaSearch\Insights\UserInsightsClient;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class InsightsClient
{
    /**
     * @var ApiWrapperInterface
     */
    private $api;

    /**
     * @var \Algolia\AlgoliaSearch\Config\InsightsConfig
     */
    private $config;

    public function __construct(ApiWrapperInterface $api, InsightsConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $config = InsightsConfig::create($appId, $apiKey, $region);

        return static::createWithConfig($config);
    }

    public static function createWithConfig(InsightsConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForInsights($config->getRegion());
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function user($userToken)
    {
        return new UserInsightsClient($this, $userToken);
    }

    public function sendEvent($event, $requestOptions = [])
    {
        return $this->sendEvents([$event], $requestOptions);
    }

    public function sendEvents($events, $requestOptions = [])
    {
        $payload = ['events' => $events];

        return $this->api->write('POST', api_path('/1/events'), $payload, $requestOptions);
    }
}

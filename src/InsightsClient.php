<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\InsightsConfig;
use Algolia\AlgoliaSearch\Insights\AbstractInsightsClient;
use Algolia\AlgoliaSearch\Insights\SearchInsightClient;
use Algolia\AlgoliaSearch\Insights\PersoInsightClient;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class InsightsClient extends AbstractInsightsClient
{
    public static function create($appId = null, $apiKey = null, $region = null, $userToken = null)
    {
        $config = InsightsConfig::create($appId, $apiKey, $region);

        if ($userToken) {
            $config->setUserToken($config);
        }

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
        $config = clone $this->config;
        $config->setUserToken($userToken);

        return new PersoInsightClient($this->api, $config);
    }
}

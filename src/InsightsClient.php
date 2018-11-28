<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\InsightsConfig;
use Algolia\AlgoliaSearch\Insights\AbstractInsightsClient;
use Algolia\AlgoliaSearch\Insights\SearchInsightClient;
use Algolia\AlgoliaSearch\Insights\VisitInsightClient;
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

    public function setUserToken($userToken)
    {
        $this->config->setUserToken($userToken);

        return $this;
    }

    public function search($queryId)
    {
        if (!$queryId) {
            throw new \InvalidArgumentException('QueryID must be a non-null string');
        }

        $search = new SearchInsightClient($this->api, $this->config);

        return $search->setQueryId($queryId);
    }

    public function visit()
    {
        return new VisitInsightClient($this->api, $this->config);
    }
}

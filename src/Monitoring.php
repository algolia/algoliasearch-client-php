<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\SimpleConfig;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Interfaces\ConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class Monitoring
{
    /** @var \Algolia\AlgoliaSearch\Monitoring */
    private static $monitoring;

    /** @var \Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper */
    private $api;

    public function __construct(ApiWrapper $apiWrapper)
    {
        $this->api = $apiWrapper;
    }

    public static function get($appId = null, $apiKey = null)
    {
        if (!static::$monitoring) {
            static::$monitoring = static::create($appId, $apiKey);
        }

        return static::$monitoring;
    }

    public static function create($appId = null, $apiKey = null)
    {
        if (null === $appId) {
            $appId = getenv('ALGOLIA_APP_ID');
        }
        if (null === $apiKey) {
            $apiKey = getenv('ALGOLIA_MONITORING_API_KEY');
        }

        $config = SimpleConfig::create($appId, $apiKey);

        return self::createWithConfig($config);
    }

    public static function createWithConfig(ConfigInterface $config)
    {
        if ($hosts = $config->getHosts()) {
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForMonitoring();
        }

        $apiWrapper = new ApiWrapper(
            HttpClientFactory::get(),
            $config,
            $clusterHosts
        );

        return new self($apiWrapper);
    }
}

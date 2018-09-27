<?php

namespace Algolia\AlgoliaSearch\Config;

class AnalyticsConfig extends AbstractConfig
{
    public static function create($appId = null, $apiKey = null)
    {
        $config = array(
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
        );

        return new static($config);
    }
}

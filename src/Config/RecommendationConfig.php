<?php

namespace Algolia\AlgoliaSearch\Config;

final class RecommendationConfig extends AbstractConfig
{
    public static function create($region, $appId = null, $apiKey = null)
    {
        if (!$region || '' === $region) {
            throw new \InvalidArgumentException('The region is required');
        }

        $config = array(
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
            'region' => $region,
        );

        return new static($config);
    }

    public function setRegion($region)
    {
        $this->config['region'] = $region;

        return $this;
    }

    public function getRegion()
    {
        return $this->config['region'];
    }
}

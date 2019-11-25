<?php

namespace Algolia\AlgoliaSearch\Config;

final class RecommendationConfig extends AbstractConfig
{
    /**
     * @param string      $region
     * @param string|null $appId
     * @param string|null $apiKey
     *
     * @return RecommendationConfig
     */
    public static function create($region, $appId = null, $apiKey = null)
    {
        if (!is_string($region) || '' === $region) {
            throw new \InvalidArgumentException('The region is required');
        }

        $config = array(
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
            'region' => $region,
        );

        return new self($config);
    }

    public function getRegion()
    {
        return $this->config['region'];
    }
}

<?php

namespace Algolia\AlgoliaSearch\Config;

final class PersonalizationConfig extends AbstractConfig
{
    /**
     * @param string|null $appId
     * @param string|null $apiKey
     * @param string|null $region
     *
     * @return PersonalizationConfig
     */
    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $config = [
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
            'region' => null !== $region ? $region : 'us',
        ];

        return new self($config);
    }

    public function getRegion()
    {
        return $this->config['region'];
    }
}

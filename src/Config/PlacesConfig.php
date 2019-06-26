<?php

namespace Algolia\AlgoliaSearch\Config;

final class PlacesConfig extends AbstractConfig
{
    /**
     * @param string $appId
     * @param string $apiKey
     *
     * @return PlacesConfig
     */
    public static function create($appId, $apiKey)
    {
        $config = array(
            'appId' => $appId,
            'apiKey' => $apiKey,
        );

        return new static($config);
    }
}

<?php

namespace Algolia\AlgoliaSearch\Config;

final class PlacesConfig extends AbstractConfig
{
    public static function create($appId, $apiKey)
    {
        $config = array(
            'appId' => $appId,
            'apiKey' => $apiKey,
        );

        return new static($config);
    }
}

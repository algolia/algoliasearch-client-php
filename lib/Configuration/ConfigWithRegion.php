<?php

namespace Algolia\AlgoliaSearch\Configuration;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;

abstract class ConfigWithRegion extends Configuration
{
    public static function create($appId, $apiKey, $region = null, $allowedRegions = null)
    {
        if ($region !== null && !in_array($region, $allowedRegions, true)) {
            throw new AlgoliaException('Specified region is not allowed.');
        }

        $config = [
            'appId' => $appId,
            'apiKey' => $apiKey,
            'region' => null !== $region ? $region : 'us',
        ];

        return new static($config);
    }

    public function getRegion()
    {
        return $this->config['region'];
    }
}

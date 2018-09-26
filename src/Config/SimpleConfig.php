<?php

namespace Algolia\AlgoliaSearch\Config;

class SimpleConfig extends AbstractConfig
{
    public static function create($appId, $apiKey)
    {
        $config = array(
            'appId' => $appId,
            'apiKey' => $apiKey,
        );

        return new static($config);
    }

    public function getDefaultConfig()
    {
        return array(
            'appId' => '',
            'apiKey' => '',
            'hosts' => null,
            'readTimeout' => $this->defaultReadTimeout,
            'writeTimeout' => $this->defaultWriteTimeout,
            'connectTimeout' => $this->defaultConnectTimeout,
        );
    }
}

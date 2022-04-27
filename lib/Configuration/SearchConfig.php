<?php

namespace Algolia\AlgoliaSearch\Configuration;

class SearchConfig extends Configuration
{
    private $defaultWaitTaskTimeBeforeRetry = 100000;

    public static function create($appId, $apiKey)
    {
        $config = [
            'appId' => $appId,
            'apiKey' => $apiKey,
        ];

        return new static($config);
    }

    public function getDefaultConfiguration()
    {
        return [
            'appId' => '',
            'apiKey' => '',
            'hosts' => null,
            'readTimeout' => $this->defaultReadTimeout,
            'writeTimeout' => $this->defaultWriteTimeout,
            'connectTimeout' => $this->defaultConnectTimeout,
            'waitTaskTimeBeforeRetry' => $this->defaultWaitTaskTimeBeforeRetry,
            'defaultHeaders' => [],
            'defaultForwardToReplicas' => null,
            'batchSize' => 1000,
        ];
    }
}

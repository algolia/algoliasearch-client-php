<?php

namespace Algolia\AlgoliaSearch\Configuration;

class SearchConfig extends Configuration
{
    protected $clientName = 'Search';
    private $defaultWaitTaskTimeBeforeRetry = 5000; // 5 sec in milliseconds
    private $defaultMaxRetries = 50;

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
            'defaultMaxRetries' => $this->defaultMaxRetries,
            'defaultHeaders' => [],
            'defaultForwardToReplicas' => null,
            'batchSize' => 1000,
        ];
    }

    public function getWaitTaskTimeBeforeRetry()
    {
        return $this->config['waitTaskTimeBeforeRetry'];
    }

    public function getDefaultMaxRetries()
    {
        return $this->config['defaultMaxRetries'];
    }
}

<?php

namespace Algolia\AlgoliaSearch\Config;

class SearchConfig extends AbstractConfig
{
    private $defaultWaitTaskTimeBeforeRetry = 100000;

    public static function create($appId = null, $apiKey = null)
    {
        $config = array(
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
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
            'waitTaskTimeBeforeRetry' => $this->defaultWaitTaskTimeBeforeRetry,
            'defaultHeaders' => array(),
            'defaultForwardToReplicas' => null,
            'batchSize' => 1000,
        );
    }

    public function getWaitTaskTimeBeforeRetry()
    {
        return $this->config['waitTaskTimeBeforeRetry'];
    }

    public function setWaitTaskTimeBeforeRetry($time)
    {
        if (!is_numeric($time)) {
            throw new \InvalidArgumentException('Time before retry must be a numeric value');
        }

        $this->config['waitTaskTimeBeforeRetry'] = $time;

        return $this;
    }

    public function getDefaultForwardToReplicas()
    {
        return $this->config['defaultForwardToReplicas'];
    }

    public function setDefaultForwardToReplicas($default)
    {
        if (!is_bool($default)) {
            throw new \InvalidArgumentException('Default configuration for ForwardToReplicas must be a boolean');
        }

        $this->config['defaultForwardToReplicas'] = $default;

        return $this;
    }

    public function getBatchSize()
    {
        return $this->config['batchSize'];
    }

    public function setBatchSize($batchSize)
    {
        if (!is_int($batchSize) || $batchSize < 1) {
            throw new \InvalidArgumentException('Batch size must be an integer greater than 0');
        }

        $this->config['batchSize'] = $batchSize;

        return $this;
    }
}

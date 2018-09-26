<?php

namespace Algolia\AlgoliaSearch\Config;

final class ClientConfig extends AbstractConfig
{
    private $defaultWaitTaskTimeBeforeRetry = 100000;

    private $defaultWaitTaskMaxRetry = 30;

    public static function create($appId = null, $apiKey = null)
    {
        $config = array();

        if (null !== $appId) {
            $config['appId'] = $appId;
        }
        if (null !== $apiKey) {
            $config['apiKey'] = $apiKey;
        }

        return new static($config);
    }

    public function getDefaultConfig()
    {
        return array(
            'appId' => getenv('ALGOLIA_APP_ID'),
            'apiKey' => getenv('ALGOLIA_API_KEY'),
            'hosts' => null,
            'readTimeout' => $this->defaultReadTimeout,
            'writeTimeout' => $this->defaultWriteTimeout,
            'connectTimeout' => $this->defaultConnectTimeout,
            'waitTaskTimeBeforeRetry' => $this->defaultWaitTaskTimeBeforeRetry,
            'waitTaskMaxRetry' => $this->defaultWaitTaskMaxRetry,
            'defaultForwardToReplicas' => null,
        );
    }

    public function getWaitTaskMaxRetry()
    {
        return $this->config['waitTaskMaxRetry'];
    }

    public function setWaitMaxTaskRetry($max)
    {
        if (!is_int($max)) {
            throw new \InvalidArgumentException('Max retry must be an integer');
        }

        $this->config['waitTaskMaxRetry'] = $max;

        return $this;
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
}

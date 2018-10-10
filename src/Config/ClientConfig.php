<?php

namespace Algolia\AlgoliaSearch\Config;

final class ClientConfig extends AbstractConfig
{
    private $defaultWaitTaskTimeBeforeRetry = 100000;

    private $defaultWaitTaskMaxRetry = 30;

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
            'waitTaskMaxRetry' => $this->defaultWaitTaskMaxRetry,
            'defaultForwardToReplicas' => null,
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
}

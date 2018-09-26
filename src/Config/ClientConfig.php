<?php

namespace Algolia\AlgoliaSearch\Config;

use Algolia\AlgoliaSearch\Log\Logger;
use Psr\Log\LoggerInterface;

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
        $this->config['waitTaskMaxRetry'] = $max;

        return $this;
    }

    public function getWaitTaskTimeBeforeRetry()
    {
        return $this->config['waitTaskTimeBeforeRetry'];
    }

    public function setWaitTaskTimeBeforeRetry($time)
    {
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

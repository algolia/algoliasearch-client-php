<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

class ClientConfig implements ClientConfigInterface
{
    private $config;

    private $defaultWaitTaskTimeBeforeRetry = 100000;
    private $defaultWaitTaskMaxRetry = 30;

    private $defaultReadTimeout = 5;
    private $defaultWriteTimeout = 5;
    private $defaultConnectTimeout = 2;

    public function __construct(array $config = array())
    {
        $config += $this->getDefaultConfig();

        if (null === $config['hosts']) {
            $config['hosts'] = ClusterHosts::createFromAppId($config['appId']);
        }

        $this->config = $config;
    }

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
        );
    }

    public function getAppId()
    {
        return $this->config['appId'];
    }

    public function setAppId($appId)
    {
        $this->config['appId'] = $appId;

        return $this;
    }

    public function getApiKey()
    {
        return $this->config['apiKey'];
    }

    public function setApiKey($apiKey)
    {
        $this->config['apiKey'] = $apiKey;

        return $this;
    }

    public function getHosts()
    {
        return $this->config['hosts'];
    }

    public function setHosts(ClusterHosts $hosts)
    {
        $this->config['hosts'] = $hosts;

        return $this;
    }

    public function getReadTimeout()
    {
        return $this->config['readTimeout'];
    }

    public function setReadTimeout($readTimeout)
    {
        $this->config['readTimeout'] = $readTimeout;

        return $this;
    }

    public function getWriteTimeout()
    {
        return $this->config['writeTimeout'];
    }

    public function setWriteTimeout($writeTimeout)
    {
        $this->config['writeTimeout'] = $writeTimeout;

        return $this;
    }

    public function getConnectTimeout()
    {
        return $this->config['connectTimeout'];
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->config['connectTimeout'] = $connectTimeout;

        return $this;
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
}

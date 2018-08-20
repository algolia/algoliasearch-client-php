<?php

namespace Algolia\AlgoliaSearch\Support;

class ClientConfig
{
    private $config;

    public function __construct($appId = null, $apiKey = null)
    {
        $config = array();
        if (null !== $appId) {
            $config['appId'] = $appId;
        }
        if (null !== $apiKey) {
            $config['apiKey'] = $apiKey;
        }

        $this->config = $config + $this->getDefaultConfig();
    }

    private function getDefaultConfig()
    {
        return array(
            'appId' => getenv('ALGOLIA_APP_ID'),
            'apiKey' => getenv('ALGOLIA_API_KEY'),
            'hosts' => array(),
            'waitTaskRetry' => Config::$waitTaskRetry,
            'readTimeout' => Config::getReadTimeout(),
            'writeTimeout' => Config::getWriteTimeout(),
            'connectTimeout' => Config::getConnectTimeout(),
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

    public function setHosts($hosts)
    {
        $this->config['hosts'] = $hosts;
        return $this;
    }

    public function getWaitTaskRetry()
    {
        return $this->config['waitTaskRetry'];
    }

    public function setWaitTaskRetry($waitTaskRetry)
    {
        $this->config['waitTaskRetry'] = $waitTaskRetry;
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
}

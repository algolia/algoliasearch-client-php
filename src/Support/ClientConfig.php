<?php

namespace Algolia\AlgoliaSearch\Support;

class ClientConfig
{
    private $config;

    public function __construct($config = array())
    {
        // We want to set default credentials
        // if they are set to null, and only null
        foreach (array('appId', 'apiKey') as $key) {
            if (isset($config[$key]) && is_null($config[$key])) {
                unset($config[$key]);
            }
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

    public function getApiKey()
    {
        return $this->config['apiKey'];
    }

    public function getHosts()
    {
        return $this->config['hosts'];
    }

    public function getWaitTaskRetry()
    {
        return $this->config['waitTaskRetry'];
    }

    public function getReadTimeout()
    {
        return $this->config['readTimeout'];
    }

    public function getWriteTimeout()
    {
        return $this->config['writeTimeout'];
    }

    public function getConnectTimeout()
    {
        return $this->config['connectTimeout'];
    }
}

<?php

namespace Algolia\AlgoliaSearch\Config;

abstract class AbstractConfig
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $defaultReadTimeout = 5;

    /**
     * @var int
     */
    protected $defaultWriteTimeout = 30;

    /**
     * @var int
     */
    protected $defaultConnectTimeout = 2;

    /**
     * AbstractConfig constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $config += $this->getDefaultConfig();

        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        return array(
            'appId' => '',
            'apiKey' => '',
            'hosts' => null,
            'readTimeout' => $this->defaultReadTimeout,
            'writeTimeout' => $this->defaultWriteTimeout,
            'connectTimeout' => $this->defaultConnectTimeout,
            'defaultHeaders' => array(),
        );
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->config['appId'];
    }

    /**
     * @param string $appId
     *
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->config['appId'] = $appId;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->config['apiKey'];
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->config['apiKey'] = $apiKey;

        return $this;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->config['hosts'];
    }

    /**
     * @param array $hosts
     *
     * @return $this
     */
    public function setHosts($hosts)
    {
        $this->config['hosts'] = $hosts;

        return $this;
    }

    /**
     * @return int
     */
    public function getReadTimeout()
    {
        return $this->config['readTimeout'];
    }

    /**
     * @param int $readTimeout
     *
     * @return $this
     */
    public function setReadTimeout($readTimeout)
    {
        $this->config['readTimeout'] = $readTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getWriteTimeout()
    {
        return $this->config['writeTimeout'];
    }

    /**
     * @param int $writeTimeout
     *
     * @return $this
     */
    public function setWriteTimeout($writeTimeout)
    {
        $this->config['writeTimeout'] = $writeTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->config['connectTimeout'];
    }

    /**
     * @param int $connectTimeout
     *
     * @return $this
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->config['connectTimeout'] = $connectTimeout;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultHeaders()
    {
        return $this->config['defaultHeaders'];
    }

    /**
     * @param array $defaultHeaders
     *
     * @return $this
     */
    public function setDefaultHeaders(array $defaultHeaders)
    {
        $this->config['defaultHeaders'] = $defaultHeaders;

        return $this;
    }
}

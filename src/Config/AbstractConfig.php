<?php

namespace Algolia\AlgoliaSearch\Config;

abstract class AbstractConfig
{
    const COMPRESSION_TYPE_NONE = 'none';
    const COMPRESSION_TYPE_GZIP = 'gzip';

    protected $config;

    protected $defaultReadTimeout = 5;

    protected $defaultWriteTimeout = 30;

    protected $defaultConnectTimeout = 2;

    public function __construct(array $config = array())
    {
        $config += $this->getDefaultConfig();

        $this->config = $config;
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
            'defaultHeaders' => array(),
            'gzipEnabled' => self::COMPRESSION_TYPE_NONE,
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

    public function getDefaultHeaders()
    {
        return $this->config['defaultHeaders'];
    }

    public function setDefaultHeaders(array $defaultHeaders)
    {
        $this->config['defaultHeaders'] = $defaultHeaders;

        return $this;
    }

    /**
     * @return bool
     */
    public function getGzipEnabled()
    {
        return $this->config['gzipEnabled'];
    }

    /**
     * @param string $gzipEnabled
     *
     * @return $this
     */
    public function setGzipEnabled($gzipEnabled)
    {
        if (!in_array(
            $gzipEnabled,
            array(self::COMPRESSION_TYPE_GZIP, self::COMPRESSION_TYPE_NONE),
            true
        )) {
            throw new \InvalidArgumentException('gzipEnabled must be equal to '.self::COMPRESSION_TYPE_GZIP.' or '.self::COMPRESSION_TYPE_NONE);
        }

        $this->config['gzipEnabled'] = $gzipEnabled;

        return $this;
    }
}

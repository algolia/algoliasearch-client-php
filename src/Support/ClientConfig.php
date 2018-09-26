<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\Log\Logger;
use Psr\Log\LoggerInterface;

class ClientConfig implements ClientConfigInterface
{
    private $config;

    /**
     * Holds an instance of the logger.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    private $defaultWaitTaskTimeBeforeRetry = 100000;

    private $defaultWaitTaskMaxRetry = 30;

    private $defaultReadTimeout = 5;

    private $defaultWriteTimeout = 5;

    private $defaultConnectTimeout = 2;

    /**
     * Holds an instance of the default logger.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private static $defaultLogger;

    public function __construct(array $config = array())
    {
        $config += $this->getDefaultConfig();

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
            'defaultForwardToReplicas' => null,
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

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger ?: self::$defaultLogger ?: new Logger();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sets the default logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setDefaultLogger(LoggerInterface $logger)
    {
        self::$defaultLogger = $logger;
    }
}

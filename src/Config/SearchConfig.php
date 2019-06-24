<?php

namespace Algolia\AlgoliaSearch\Config;

class SearchConfig extends AbstractConfig
{
    /**
     * @var int
     */
    private $defaultWaitTaskTimeBeforeRetry = 100000;

    /**
     * @param string|null $appId
     * @param string|null $apiKey
     * @return SearchConfig
     */
    public static function create($appId = null, $apiKey = null)
    {
        $config = array(
            'appId' => null !== $appId ? $appId : getenv('ALGOLIA_APP_ID'),
            'apiKey' => null !== $apiKey ? $apiKey : getenv('ALGOLIA_API_KEY'),
        );

        return new static($config);
    }

    /**
     * @return array<string, string|int|array>
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
            'waitTaskTimeBeforeRetry' => $this->defaultWaitTaskTimeBeforeRetry,
            'defaultHeaders' => array(),
            'defaultForwardToReplicas' => null,
            'batchSize' => 1000,
        );
    }

    /**
     * @return int
     */
    public function getWaitTaskTimeBeforeRetry()
    {
        return $this->config['waitTaskTimeBeforeRetry'];
    }

    /**
     * @param int $time
     * @return $this
     */
    public function setWaitTaskTimeBeforeRetry($time)
    {
        if (!is_numeric($time)) {
            throw new \InvalidArgumentException('Time before retry must be a numeric value');
        }

        $this->config['waitTaskTimeBeforeRetry'] = $time;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDefaultForwardToReplicas()
    {
        return $this->config['defaultForwardToReplicas'];
    }

    /**
     * @param bool $default
     * @return $this
     */
    public function setDefaultForwardToReplicas($default)
    {
        $this->config['defaultForwardToReplicas'] = $default;

        return $this;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->config['batchSize'];
    }

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        if ($batchSize < 1) {
            throw new \InvalidArgumentException('Batch size must be an integer greater than 0');
        }

        $this->config['batchSize'] = $batchSize;

        return $this;
    }
}

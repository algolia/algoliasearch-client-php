<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Config\ClientConfig;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

class DeleteApiKeyResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \Algolia\AlgoliaSearch\Config\ClientConfig
     */
    private $config;

    /**
     * @var string API Key to be deleted
     */
    private $key;

    public function __construct(array $apiResponse, ClientInterface $client, ClientConfig $config, $key)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
        $this->key = $key;
    }

    public function wait($requestOptions = array())
    {
        if (!isset($this->client)) {
            return $this;
        }

        $retry = 1;
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            try {
                $this->client->getApiKey($this->key, $requestOptions);
            } catch (NotFoundException $e) {
                unset($this->client, $this->config);

                return $this;
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while (true);
    }
}

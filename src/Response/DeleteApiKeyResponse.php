<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;

final class DeleteApiKeyResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    private $client;

    /**
     * @var \Algolia\AlgoliaSearch\Config\SearchConfig
     */
    private $config;

    /**
     * @var string API Key to be deleted
     */
    private $key;

    public function __construct(array $apiResponse, SearchClient $client, SearchConfig $config, $key)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
        $this->key = $key;
    }

    public function wait($requestOptions = [])
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

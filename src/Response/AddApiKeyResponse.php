<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;

final class AddApiKeyResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    private $client;

    /**
     * @var \Algolia\AlgoliaSearch\Config\SearchConfig
     */
    private $config;

    public function __construct(array $apiResponse, SearchClient $client, SearchConfig $config)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
    }

    public function wait($requestOptions = array())
    {
        if (!isset($this->client)) {
            return $this;
        }

        $key = $this->apiResponse['key'];
        $retry = 1;
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            try {
                $this->client->getApiKey($key, $requestOptions);

                unset($this->client, $this->config);

                return $this;
            } catch (NotFoundException $e) {
                // Try again
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while (true);
    }
}

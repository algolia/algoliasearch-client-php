<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;

final class RestoreApiKeyResponse extends AbstractResponse
{
    /**
     * @var SearchClient
     */
    private $client;

    /**
     * @var SearchConfig
     */
    private $config;

    /**
     * @var string API Key to be deleted
     */
    private $key;

    /**
     * RestoreApiKeyResponse constructor.
     *
     * @param array                                      $apiResponse
     * @param SearchClient        $client
     * @param SearchConfig $config
     * @param string                                     $key
     */
    public function __construct(array $apiResponse, SearchClient $client, SearchConfig $config, $key)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
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

                unset($this->client, $this->config);

                return $this;
            } catch (NotFoundException $e) {
                // @ignoreException
                // Try again
            }

            $retry++;
            usleep(($retry / 10) * $time); // 0.1 second
        } while (true);
    }
}

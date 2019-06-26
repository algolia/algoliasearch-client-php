<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;

final class AddApiKeyResponse extends AbstractResponse
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
     * AddApiKeyResponse constructor.
     *
     * @param array        $apiResponse
     * @param SearchClient $client
     * @param SearchConfig $config
     */
    public function __construct(array $apiResponse, SearchClient $client, SearchConfig $config)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
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
                // @ignoreException
            }

            $retry++;
            usleep(($retry / 10) * $time); // 0.1 second
        } while (true);
    }
}

<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;
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

    public function __construct(array $apiResponse, ClientInterface $client, ClientConfig $config)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
    }

    public function wait($requestOptions = array())
    {
        if (!$this->client) {
            return $this;
        }

        $key = $this->apiResponse['value'];
        $retry = 1;
        $maxRetry = $this->config->getWaitTaskMaxRetry();
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            try {
                $this->client->getApiKey($key, $requestOptions);

            } catch (NotFoundException $e) {
                unset($this->client, $this->config);
                return $this;
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException('The key '.substr($key, 0, 6)."... isn't added yet.");
    }
}

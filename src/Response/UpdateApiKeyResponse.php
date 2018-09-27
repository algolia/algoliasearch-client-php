<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Config\ClientConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;

class UpdateApiKeyResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Client
     */
    private $client;

    /**
     * @var \Algolia\AlgoliaSearch\Config\ClientConfig
     */
    private $config;

    private $keyParams;

    public function __construct(
        array $apiResponse,
        Client $client,
        ClientConfig $config,
        $keyParams
    ) {

        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
        $this->keyParams = $keyParams;
    }

    public function wait($requestOptions = array())
    {
        if (!isset($this->client)) {
            return $this;
        }

        $key = $this->apiResponse['key'];
        $retry = 1;
        $maxRetry = $this->config->getWaitTaskMaxRetry();
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            try {
                $this->client->getApiKey($key, $requestOptions);

                if ($this->isKeyUpdated($key, $this->keyParams)) {
                    unset($this->client, $this->config);

                    return $this;
                }
            } catch (NotFoundException $e) {
                // Try again
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException('The key '.substr($key, 0, 6)."... isn't updated yet.");
    }

    private function isKeyUpdated($key, $keyParams)
    {
        $upToDate = true;
        foreach ($keyParams as $param => $value) {
            if (isset($key[$param])) {
                $upToDate &= ($key[$param] == $value);
            }
        }

        return $upToDate;
    }
}

<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\SearchClient;

final class UpdateApiKeyResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    private $client;

    /**
     * @var \Algolia\AlgoliaSearch\Config\SearchConfig
     */
    private $config;

    private $keyParams;

    public function __construct(
        array $apiResponse,
        SearchClient $client,
        SearchConfig $config,
        $requestOptions
    ) {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
        $this->config = $config;
        $this->keyParams = $this->filterOnlyKeyParams($requestOptions);
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
        } while (true);
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

    private function filterOnlyKeyParams($requestOptions)
    {
        $validKeyParams = array(
            'acl',  'indexes',  'referers',
            'restrictSources', 'queryParameters',  'description',
            'validity',  'maxQueriesPerIPPerHour',  'maxHitsPerQuery',
        );

        return array_intersect_key($requestOptions, array_flip($validKeyParams));
    }
}

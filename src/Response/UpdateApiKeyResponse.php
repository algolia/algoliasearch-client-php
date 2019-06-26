<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

final class UpdateApiKeyResponse extends AbstractResponse
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
     * @var array
     */
    private $keyParams;

    /**
     * UpdateApiKeyResponse constructor.
     *
     * @param array        $apiResponse
     * @param SearchClient $client
     * @param SearchConfig $config
     * @param array        $requestOptions
     */
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

                if ($this->isKeyUpdated($key, $this->keyParams)) {
                    unset($this->client, $this->config);

                    return $this;
                }
            } catch (NotFoundException $e) {
                // Try again
                // @ignoreException
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while (true);
    }

    /**
     * @param string $key
     * @param array  $keyParams
     *
     * @return bool
     */
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

    /**
     * @param array $requestOptions
     *
     * @return array
     */
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

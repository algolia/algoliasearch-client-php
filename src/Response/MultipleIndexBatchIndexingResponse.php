<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\SearchClient;

final class MultipleIndexBatchIndexingResponse extends AbstractResponse
{
    /**
     * @var SearchClient
     */
    private $client;

    /**
     * MultipleIndexBatchIndexingResponse constructor.
     *
     * @param array        $apiResponse
     * @param SearchClient $client
     */
    public function __construct(array $apiResponse, SearchClient $client)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
    }

    /**
     * @param array $requestOptions
     *
     * @return $this
     */
    public function wait($requestOptions = array())
    {
        if (!isset($this->client)) {
            return $this;
        }

        foreach ($this->apiResponse['taskID'] as $indexName => $taskId) {
            $this->client->waitTask($indexName, $taskId, $requestOptions);
        }

        unset($this->client);

        return $this;
    }
}

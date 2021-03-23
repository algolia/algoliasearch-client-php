<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\SearchClient;

final class MultipleIndexBatchIndexingResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    private $client;

    public function __construct(array $apiResponse, SearchClient $client)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
    }

    public function wait($requestOptions = [])
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

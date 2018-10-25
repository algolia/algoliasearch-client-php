<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Interfaces\SearchClientInterface;

class MultipleIndexingResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\SearchClientInterface
     */
    private $client;

    public function __construct(array $apiResponse, SearchClientInterface $client)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
    }

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

<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

class MultipleIndexingResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\ClientInterface
     */
    private $client;

    public function __construct(array $apiResponse, ClientInterface $client)
    {
        $this->apiResponse = $apiResponse;
        $this->client = $client;
    }

    public function wait($requestOptions = array())
    {
        if (!$this->client) {
            return $this;
        }

        foreach ($this->apiResponse['taskId'] as $indexName => $taskId) {
            $this->client->waitTask($indexName, $taskId, $requestOptions);
        }

        unset($this->client);

        return $this;
    }
}

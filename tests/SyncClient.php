<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Client;

class SyncClient
{
    /**
     * @var \Algolia\AlgoliaSearch\Client
     */
    private $realClient;

    public function __construct(Client $realClient)
    {
        $this->realClient = $realClient;
    }

    public function initIndex($indexName)
    {
        return new SyncIndex(
            $this->realClient->initIndex($indexName)
        );
    }

    public function moveIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        $response = $this->realClient->moveIndex($srcIndexName, $destIndexName, $requestOptions);
        $this->realClient->waitTask($srcIndexName, $response['taskID']);

        return $response;
    }

    public function copyIndex($srcIndexName, $destIndexName, $requestOptions = array())
    {
        $response = $this->realClient->copyIndex($srcIndexName, $destIndexName, $requestOptions);
        $this->realClient->waitTask($srcIndexName, $response['taskID']);

        return $response;
    }

    public function clearIndex($indexName, $requestOptions = array())
    {
        $response = $this->realClient->clearIndex($indexName, $requestOptions);
        $this->realClient->waitTask($indexName, $response['taskID']);

        return $response;
    }

    public function deleteIndex($indexName, $requestOptions = array())
    {
        $response = $this->realClient->deleteIndex($indexName, $requestOptions);
        $this->realClient->waitTask($indexName, $response['taskID']);

        return $response;
    }

    public function __call($name, $arguments)
    {
        $response = call_user_func_array(array($this->realClient, $name), $arguments);

        if (is_array($response) && isset($response['taskID']) && is_array($response['taskID'])) {
            foreach ($response['taskID'] as $indexName => $taskId) {
                $this->realClient->waitTask($indexName, $taskId);
            }
        }

        return $response;
    }
}

<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Response\AbstractResponse;

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

    public function __call($name, $arguments)
    {
        $response = call_user_func_array(array($this->realClient, $name), $arguments);

        if ($response instanceof AbstractResponse) {
            $response->wait();
        }

        return $response;
    }
}

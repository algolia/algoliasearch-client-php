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

    public function index($indexName)
    {
        return new SyncIndex(
            $this->realClient->index($indexName)
        );
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->realClient, $name), $arguments);
    }
}

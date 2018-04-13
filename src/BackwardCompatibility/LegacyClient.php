<?php

namespace Algolia\AlgoliaSearch\BackwardCompatibility;

use Algolia\AlgoliaSearch\Client;

class LegacyClient
{
    /**
     * @var \Algolia\AlgoliaSearch\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function initIndex($indexName)
    {
        // TODO: Throw warning
        return $this->client->index($indexName);
    }

    public function listUserKeys()
    {
        // TODO: Throw warning
        return $this->client->listApiKeys();
    }

    public function __call($name, $arguments)
    {
        return call_user_func(array($this->client, $name), $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func(array(Client::class, $name), $arguments);

    }
}

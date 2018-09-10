<?php

namespace Algolia\AlgoliaSearch\Canary;

use Algolia\AlgoliaSearch\Client;

class CanaryClient extends Client
{
    public function initIndex($indexName)
    {
        return new CanaryIndex($indexName, $this->api, $this->config);
    }

    /*
     * When new features are coming to Algolia, they might be added here first
     * so you can easily use them in your implementations
     */
}

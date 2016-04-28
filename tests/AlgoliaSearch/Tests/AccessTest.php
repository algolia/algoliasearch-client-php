<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;

class AccessTest extends AlgoliaSearchTestCase
{
    public function testHTTPAccess()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), ['http://'.getenv('ALGOLIA_APPLICATION_ID').'-1.algolia.io']);
        $client->isAlive();
    }

    public function testHTTPSAccess()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), ['https://'.getenv('ALGOLIA_APPLICATION_ID').'-1.algolia.io']);
        $client->isAlive();
    }

    public function testAccessWithOptions()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), ['https://'.getenv('ALGOLIA_APPLICATION_ID').'-1.algolia.io'],
                                ['curloptions' => ['CURLOPT_FAILONERROR' => 0]]);
        $client->isAlive();
    }
}

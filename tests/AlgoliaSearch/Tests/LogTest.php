<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;

class LogTest extends AlgoliaSearchTestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
    }

    public function testLogIndex()
    {
        $res = $this->client->getLogs(0, 20, true);

        $this->assertGreaterThan(0, count($res['logs']));
    }
}

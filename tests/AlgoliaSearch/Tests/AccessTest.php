<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;

class AccessTest extends AlgoliaSearchTestCase
{
    public function testHTTPAccess()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), array(
            'http://'.getenv('ALGOLIA_APPLICATION_ID').'-dsn.algolia.net',
            'http://'.getenv('ALGOLIA_APPLICATION_ID').'-1.algolianet.com'
        ));

        $client->isAlive();
    }

    public function testHTTPSAccess()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), array(
            'https://'.getenv('ALGOLIA_APPLICATION_ID').'-dsn.algolia.net',
            'https://'.getenv('ALGOLIA_APPLICATION_ID').'-1.algolianet.com'
        ));

        $client->isAlive();
    }

    public function testAccessWithOptions()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), null, array('curloptions' => array('CURLOPT_FAILONERROR' => 0)));

        $client->isAlive();
    }
}

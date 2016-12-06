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

    public function testStatefullRetryStrategy()
    {
        if (version_compare(phpversion(), '5.4', '<')) {
            $this->markTestSkipped("No way to test statefull retry strategy in Travis for PHP 5.3.");
        }

        $client = new Client(
            getenv('ALGOLIA_APPLICATION_ID'),
            getenv('ALGOLIA_API_KEY'),
            array(
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.biz', // Unresolvable.
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.net',
            )
        );
        $start = microtime(true);
        for ($i = 0; $i < 10; $i++) {
            $client->isAlive();
        }
        $processingTime = microtime(true) - $start;
        $this->assertLessThan(5, $processingTime);
    }
}

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

    public function testDnsFallback()
    {
        // Do not run this test on Travis-CI as there is AsynchDNS disabled
        if (getenv('TRAVIS') == true) {
            return;
        }

        $client = new Client(
            getenv('ALGOLIA_APPLICATION_ID'),
            getenv('ALGOLIA_API_KEY'),
            array(
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.biz', // Assuming .biz will always fail to resolve
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.net'
            )
        );

        $start = microtime(true);
        $client->listIndexes();
        $processingTime = microtime(true) - $start;

        // Timeout of DNS resolving is set to 2 seconds
        // Total processing should be between 2 and 4 seconds
        $this->assertGreaterThanOrEqual(2, $processingTime);
        $this->assertLessThanOrEqual(5, $processingTime);
    }
}

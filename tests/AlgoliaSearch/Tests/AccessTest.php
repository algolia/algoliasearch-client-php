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
        $client = $this->getNewClient();
        $start = microtime(true);
        $client->listIndexes();
        $processingTime = microtime(true) - $start;
        // Timeout of DNS resolving is set to 1 seconds
        // Total processing should be between 1 and 3 seconds
        $this->assertGreaterThanOrEqual(1, round($processingTime));
        $this->assertLessThanOrEqual(3, round($processingTime));
    }

    public function testDnsFallbackForSeveralRequests()
    {
        $client = $this->getNewClient();

        $start = microtime(true);
        for ($i = 0; $i < 10; $i++) {
            $client->isAlive();
        }

        $processingTime = microtime(true) - $start;
        // Total processing should be between 5 seconds
        $this->assertLessThanOrEqual(5, round($processingTime));
    }

    private function getNewClient()
    {
        return new Client(
            getenv('ALGOLIA_APPLICATION_ID'),
            getenv('ALGOLIA_API_KEY'),
            array(
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.biz', // Assuming .biz will always fail to resolve
                getenv('ALGOLIA_APPLICATION_ID') . '.algolia.net'
            )
        );
    }

    public function testDnsFallbackForSeveralInstance()
    {
        $start = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            $client = $this->getNewClient();
            $client->isAlive();
        }

        $processingTime = microtime(true) - $start;
        // Total processing should be between 5 seconds
        $this->assertLessThanOrEqual(5, round($processingTime));
    }
}

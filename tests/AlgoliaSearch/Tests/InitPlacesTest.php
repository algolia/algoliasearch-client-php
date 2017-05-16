<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;

class InitPlacesTest extends AlgoliaSearchTestCase
{
    public function testInitPlaces()
    {
        $placesIndex = Client::initPlaces(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->assertInstanceOf('\AlgoliaSearch\PlacesIndex', $placesIndex);
    }

    public function testExtraHeader()
    {
        $placesIndex = Client::initPlaces(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $placesIndex->setExtraHeader('X-Forwarded-For', 'test');

        $this->assertArrayHasKey('X-Forwarded-For', $placesIndex->getContext()->headers);
        $this->assertEquals('test', $placesIndex->getContext()->headers['X-Forwarded-For']);
    }

    public function testShouldAllowToBeCalledWithoutCredentials()
    {
        Client::initPlaces();
    }

    /**
     * @depends testShouldAllowToBeCalledWithoutCredentials
     */
    public function testCanSearchWithoutCredentials()
    {
        $index = Client::initPlaces();
        $results = $index->search('');
        $this->assertArrayHasKey('nbHits', $results);
        $this->assertGreaterThan(0, $results['nbHits']);
    }
}

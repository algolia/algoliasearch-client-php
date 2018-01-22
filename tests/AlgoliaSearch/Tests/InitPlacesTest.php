<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;
use AlgoliaSearch\ClientContext;
use ReflectionObject;

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
        $this->assertTrue(true);
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

    public function testPlacesHostsAreCorrect()
    {
        $placesContext = new ClientContext(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), null, true);

        $refl = new ReflectionObject($placesContext);
        $readHostsProperty = $refl->getProperty('readHostsArray');
        $writeHostsProperty = $refl->getProperty('writeHostsArray');
        $readHostsProperty->setAccessible(true);
        $writeHostsProperty->setAccessible(true);

        $readHosts = $readHostsProperty->getValue($placesContext);
        $writeHosts = $writeHostsProperty->getValue($placesContext);

        $this->assertArraySubset(array('places-dsn.algolia.net'), $readHosts);
        $this->assertArraySubset(array('places.algolia.net'), $writeHosts);
    }
}

<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;

class PlacesIndexTest extends AlgoliaSearchTestCase
{
    public function testGetObject()
    {
        $placesIndex = Client::initPlaces();
        $response = $placesIndex->getObject('171457082_7444');

        $this->assertEquals('171457082_7444', $response['objectID']);
    }
}

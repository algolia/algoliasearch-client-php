<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;
use AlgoliaSearch\PlacesIndex;

class PlacesIndexTest extends AlgoliaSearchTestCase
{
    public function testGetObject()
    {
        /** @var PlacesIndex $placesIndex */
        $placesIndex = Client::initPlaces();
        $response = $placesIndex->search('Paris', array('hitsPerPage' => 12));

        $this->assertCount(12, $response['hits']);
    }
}

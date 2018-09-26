<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Places;

class PlacesTest extends AlgoliaIntegrationTestCase
{
    public function testSearch()
    {
        $places = Places::create();
        $requestOptions = array('hitsPerPage' => 2, 'type' => 'country');
        $response = $places->search('paris', $requestOptions);

        parse_str($response['params'], $params);
        $this->assertArraySubset($requestOptions, $params);
    }

    public function testGetObject()
    {
        $places = Places::create();
        $response = $places->getObject('4637652be1b003cfe1e7bccc1abd833e');

        $subset = array(
            'is_highway' => false,
            'country_code' => 'fr',
            'is_city' => true,
            'district' => 'Paris',
        );

        $this->assertArraySubset($subset, $response);
    }
}

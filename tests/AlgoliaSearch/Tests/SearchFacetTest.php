<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class SearchFacetTest extends AlgoliaSearchTestCase
{
    private $client;
    /** @var \AlgoliaSearch\Index */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testSearchTest()
    {
        $settings = array(
            'attributesForFaceting' => array('searchable(series)', 'kind')
        );

        $objects = array(
            array(
                'objectID' => '1',
                'name'     => 'Snoopy',
                'kind'     => array('dog', 'animal'),
                'born'     => 1950,
                'series'   => 'Peanuts'
            ),
            array(
                'objectID' => '2',
                'name'     => 'Woodstock',
                'kind'     => array('bird', 'animal'),
                'born'     => 1960,
                'series'   => 'Peanuts'
            ),
            array(
                'objectID' => '3',
                'name'     => 'Charlie Brown',
                'kind'     => array('human'),
                'born'     => 1950,
                'series'   => 'Peanuts'
            ),
            array(
                'objectID' => '4',
                'name'     => 'Hobbes',
                'kind'     => array('tiger', 'animal', 'teddy'),
                'born'     => 1985,
                'series'   => 'Calvin & Hobbes'
            ),
            array(
                'objectID' => '5',
                'name'     => 'Calvin',
                'kind'     => array('human'),
                'born'     => 1985,
                'series'   => 'Calvin & Hobbes'
            )
        );

        $this->index->setSettings($settings);
        $task = $this->index->addObjects($objects);
        $this->index->waitTask($task['taskID']);

        # Straightforward search.
        $facetHits = $this->index->searchFacet('series', 'Hobb');
        $facetHits = $facetHits['facetHits'];
        $this->assertEquals(count($facetHits), 1);
        $this->assertEquals($facetHits[0]['value'], 'Calvin & Hobbes');
        $this->assertEquals($facetHits[0]['count'], 2);

        # Using an addition query to restrict search.
        $query = array(
            'facetFilters'   => 'kind:animal',
            'numericFilters' => 'born >= 1955'
        );
        $facetHits = $this->index->searchFacet('series', 'Peanutz', $query);
        $facetHits = $facetHits['facetHits'];
        $this->assertEquals($facetHits[0]['value'], 'Peanuts');
        $this->assertEquals($facetHits[0]['count'], 1);
    }

    public function testSearchForFacetValuesTest()
    {
        $settings = array(
            'attributesForFaceting' => array('searchable(series)', 'kind')
        );

        $objects = array(
            array(
                'objectID' => '1',
                'name' => 'Snoopy',
                'kind' => array('dog', 'animal'),
                'born' => 1950,
                'series' => 'Peanuts'
            ),
            array(
                'objectID' => '2',
                'name' => 'Woodstock',
                'kind' => array('bird', 'animal'),
                'born' => 1960,
                'series' => 'Peanuts'
            ),
            array(
                'objectID' => '3',
                'name' => 'Charlie Brown',
                'kind' => array('human'),
                'born' => 1950,
                'series' => 'Peanuts'
            ),
            array(
                'objectID' => '4',
                'name' => 'Hobbes',
                'kind' => array('tiger', 'animal', 'teddy'),
                'born' => 1985,
                'series' => 'Calvin & Hobbes'
            ),
            array(
                'objectID' => '5',
                'name' => 'Calvin',
                'kind' => array('human'),
                'born' => 1985,
                'series' => 'Calvin & Hobbes'
            )
        );

        $this->index->setSettings($settings);
        $task = $this->index->addObjects($objects);
        $this->index->waitTask($task['taskID']);

        # Straightforward search.
        $facetHits = $this->index->searchForFacetValues('series', 'Hobb');
        $facetHits = $facetHits['facetHits'];
        $this->assertEquals(count($facetHits), 1);
        $this->assertEquals($facetHits[0]['value'], 'Calvin & Hobbes');
        $this->assertEquals($facetHits[0]['count'], 2);

        # Using an addition query to restrict search.
        $query = array(
            'facetFilters' => 'kind:animal',
            'numericFilters' => 'born >= 1955'
        );
        $facetHits = $this->index->searchForFacetValues('series', 'Peanutz', $query);
        $facetHits = $facetHits['facetHits'];
        $this->assertEquals($facetHits[0]['value'], 'Peanuts');
        $this->assertEquals($facetHits[0]['count'], 1);
    }
}

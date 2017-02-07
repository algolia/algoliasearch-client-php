<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class DisjunctiveFacetingTest extends AlgoliaSearchTestCase
{
    private $client;
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

        $this->index->setSettings(array(
            'attributesForFaceting' => array('category', 'color', 'brand')
        ));

        $task = $this->index->addObjects(array(
            array(
                'name' => 'red shirt 1',
                'category' => 'shirt',
                'color' => 'red',
                'brand' => 'adidas'
            ),
            array(
                'name' => 'red shirt 2',
                'category' => 'shirt',
                'color' => 'red',
                'brand' => 'nike'
            ),
            array(
                'name' => 'blue pants 1',
                'category' => 'pants',
                'color' => 'blue',
                'brand' => 'puma'
            ),
            array(
                'name' => 'blue shoes 1',
                'category' => 'shoes',
                'color' => 'blue',
                'brand' => 'puma'
            ),
            array(
                'name' => 'blue shoes 2',
                'category' => 'shoes',
                'color' => 'blue',
                'brand' => 'adidas'
            ),
            array(
                'name' => 'green shoes 1',
                'category' => 'shoes',
                'color' => 'green',
                'brand' => 'adidas'
            )
        ));

        $this->index->waitTask($task['taskID']);
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testSearchDisjunctiveWithoutRefinements()
    {
        $res1 = $this->index->search('s', array('facets' => array('color')));
        $res2 = $this->index->search('s', array('disjunctiveFacets' => array('color')));

        $this->assertEquals($res1['hits'], $res2['hits']);
        $this->assertEquals($res1['facets'], $res2['facets']);
    }

    public function testSearchDisjunctiveFacets()
    {
        $res1 = $this->index->search('s', array('facets' => array('color', 'brand')));

        $this->assertArrayHasKey('color', $res1['facets']);
        $this->assertArrayHasKey('brand', $res1['facets']);
        $this->assertEquals(2, $res1['facets']['color']['red']);
        $this->assertEquals(1, $res1['facets']['brand']['puma']);

        $res2 = $this->index->search('s', array('facets' => array('color'), 'disjunctiveFacets' => array('brand')));

        $this->assertArrayHasKey('color', $res2['facets']);
        $this->assertArrayHasKey('brand', $res2['facets']);
        $this->assertEquals(2, $res2['facets']['color']['red']);
        $this->assertEquals(1, $res2['facets']['brand']['puma']);

        $res3 = $this->index->search('', array('facetFilters' => array('color:red'), 'facets' => array('brand', 'color')));

        $this->assertArrayNotHasKey('blue', $res3['facets']['color']);
        $this->assertCount(1, $res3['facets']['color']);

        $res4 = $this->index->search('', array('facetFilters' => array('color:red'), 'facets' => array('brand'), 'disjunctiveFacets' => array('color')));

        $this->assertEquals(3, $res4['facets']['color']['blue']);
        $this->assertCount(3, $res4['facets']['color']);

        $res5 = $this->index->search('s', array('facetFilters' => array('brand:adidas'), 'facets' => array('color'), 'disjunctiveFacets' => array('brand')));

        $this->assertEquals(1, $res5['facets']['color']['red']);
        $this->assertCount(3, $res5['facets']['color']);
        $this->assertCount(3, $res5['facets']['brand']);

        $res6 = $this->index->search('s', array('facetFilters' => array('brand:adidas', 'color:red'), 'facets' => array('color'), 'disjunctiveFacets' => array('brand')));

        $this->assertEquals(1, $res6['facets']['color']['red']);
        $this->assertCount(1, $res6['facets']['color']);
        $this->assertCount(2, $res6['facets']['brand']);
        $this->assertEquals(1, $res6['facets']['brand']['nike']);

        $res1 = $this->index->search('s', array('facetFilters' => array('brand:adidas', 'color:red'), 'facets' => array(), 'disjunctiveFacets' => array('color', 'brand')));

        $this->assertEquals(1, $res1['facets']['color']['red']);
        $this->assertCount(3, $res1['facets']['color']);
        $this->assertCount(2, $res1['facets']['brand']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSearchDisjunctiveWithFilter()
    {
        $this->index->search('s', array('filters' => 'brand:adidas AND color:red', 'facets' => array(), 'disjunctiveFacets' => array('color', 'brand')));
    }
}

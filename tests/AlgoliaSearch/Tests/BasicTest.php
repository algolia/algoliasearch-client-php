<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class BasicTest extends AlgoliaSearchTestCase
{
    /** @var  Client */
    private $client;

    /** @var Index */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), null, array('cainfo' => (__DIR__.'/../../../resources/ca-bundle.crt')));
        $this->client->setConnectTimeout(1);
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

    public function testAddObject()
    {
        $res = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
    }

    public function testAddObjects()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'Robin'),
            array('firstname' => 'Robert')
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    public function testSaveObject()
    {
        $res = $this->index->saveObject(array('firstname' => 'Robin', 'objectID' => 'à/go/?à'));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(1, $results['nbHits']);
    }

    public function testSaveObjects()
    {
        $res = $this->index->saveObjects(array(
            array('firstname' => 'Robin', 'objectID' => 'à/go/?à'),
            array('firstname' => 'Robert', 'objectID' => 'à/go/?à2')
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    public function testPartialUpdateObject()
    {
        $res = $this->index->partialUpdateObject(array('lastname' => 'Oneil', 'objectID' => 'à/go/?à'));
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('Oneil');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Oneil', $results['hits'][0]['lastname']);
    }

    public function testPartialUpdateObjects()
    {
        $res = $this->index->partialUpdateObjects(array(
            array('lastname' => 'Oneil', 'objectID' => 'à/go/?à')));
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('Oneil');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Oneil', $results['hits'][0]['lastname']);
    }

    public function testDeleteObjects()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'Robin', 'objectID' => 'à/go/?à'),
            array('firstname' => 'Robert', 'objectID' => 'à/go/?à2')
        ));
        $this->index->waitTask($res['taskID']);
        $res = $this->index->deleteObjects(array('à/go/?à', 'à/go/?à2'));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(0, $results['nbHits']);
    }

    public function testDeleteObjectByQuery()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'Robin', 'objectID' => 'à/go/?à'),
            array('firstname' => 'Robert', 'objectID' => 'à/go/?à2'),
            array('firstname' => 'Robert', 'objectID' => 'à/go/?à3')
        ));
        $this->index->waitTask($res['taskID']);
        $deletedCount = $this->index->deleteByQuery('Robert');

        $this->assertEquals(2, $deletedCount);

        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
    }

    public function testMultipleQueries()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'John'),
            array('firstname' => 'Robin'),
            array('firstname' => 'William'),
        ));

        $this->index->waitTask($res['taskID']);

        $results = $this->client->multipleQueries(array(
            array(
                'indexName' => $this->safe_name('àlgol?à-php'),
                'query'     => '',
            ),
        ));

        $this->assertEquals(3, $results['results'][0]['nbHits']);
        $this->assertEquals(3, count($results['results'][0]['hits']));

        $results = $this->client->multipleQueries(array(
            array(
                'indexName'   => $this->safe_name('àlgol?à-php'),
                'query'       => '',
                'hitsPerPage' => 1,
            ),
            array(
                'indexName'   => $this->safe_name('àlgol?à-php'),
                'query'       => '',
                'hitsPerPage' => 1,
            ),
            array(
                'indexName'   => $this->safe_name('àlgol?à-php'),
                'query'       => '',
                'hitsPerPage' => 1,
            ),
        ), 'indexName', 'stopIfEnoughMatches');

        $this->assertEquals(1, count($results['results'][0]['hits']));
        $this->assertEquals(1, count($results['results'][0]['hits']) + count($results['results'][1]['hits']) + count($results['results'][2]['hits']));
    }

    public function testDisjunctiveFaceting()
    {
        $this->index->setSettings(array('attributesForFacetting' => array('city', 'stars', 'facilities')));
        $task = $this->index->addObjects(array(
            array('name' => 'Hotel A', 'stars' => '*', 'facilities' => array('wifi', 'batch', 'spa'), 'city' => 'Paris'),
            array('name' => 'Hotel B', 'stars' => '*', 'facilities' => array('wifi'), 'city' => 'Paris'),
            array('name' => 'Hotel C', 'stars' => '**', 'facilities' => array('batch'), 'city' => 'San Francisco'),
            array('name' => 'Hotel D', 'stars' => '****', 'facilities' => array('spa'), 'city' => 'Paris'),
            array('name' => 'Hotel E', 'stars' => '****', 'facilities' => array('spa'), 'city' => 'New York'))
        );
        $this->index->waitTask($task['taskID']);

        $answer = $this->index->searchDisjunctiveFaceting('h', array('stars', 'facilities'), array('facets' => 'city'));
        $this->assertEquals(5, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));

        $answer = $this->index->searchDisjunctiveFaceting('h', array('stars', 'facilities'), array('facets' => 'city'), array('stars' => array('*')));
        $this->assertEquals(2, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['**']);
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['****']);

        $answer = $this->index->searchDisjunctiveFaceting('h', array('stars', 'facilities'), array('facets' => 'city'), array('stars' => array('*'), 'city' => array('Paris')));
        $this->assertEquals(2, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['****']);

        $answer = $this->index->searchDisjunctiveFaceting('h', array('stars', 'facilities'), array('facets' => 'city'), array('stars' => array('*', '****'), 'city' => array('Paris')));
        $this->assertEquals(3, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['****']);
    }
}

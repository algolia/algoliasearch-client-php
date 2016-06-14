<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class BasicTest extends AlgoliaSearchTestCase
{
    private $client;
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), null, ['cainfo' => (__DIR__.'/../../../resources/ca-bundle.crt')]);
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
        $res = $this->index->addObject(['firstname' => 'Robin']);
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
    }

    public function testAddObjects()
    {
        $res = $this->index->addObjects([
            ['firstname' => 'Robin'],
            ['firstname' => 'Robert']
        ]);
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    public function testSaveObject()
    {
        $res = $this->index->saveObject(['firstname' => 'Robin', 'objectID' => 'à/go/?à']);
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(1, $results['nbHits']);
    }

    public function testSaveObjects()
    {
        $res = $this->index->saveObjects([
            ['firstname' => 'Robin', 'objectID' => 'à/go/?à'],
            ['firstname' => 'Robert', 'objectID' => 'à/go/?à2']
        ]);
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    public function testPartialUpdateObject()
    {
        $res = $this->index->partialUpdateObject(['lastname' => 'Oneil', 'objectID' => 'à/go/?à']);
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('Oneil');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Oneil', $results['hits'][0]['lastname']);
    }

    public function testPartialUpdateObjects()
    {
        $res = $this->index->partialUpdateObjects([
            ['lastname' => 'Oneil', 'objectID' => 'à/go/?à']]);
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('Oneil');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Oneil', $results['hits'][0]['lastname']);
    }

    public function testDeleteObjects()
    {
        $res = $this->index->addObjects([
            ['firstname' => 'Robin', 'objectID' => 'à/go/?à'],
            ['firstname' => 'Robert', 'objectID' => 'à/go/?à2']
        ]);
        $this->index->waitTask($res['taskID']);
        $res = $this->index->deleteObjects(['à/go/?à', 'à/go/?à2']);
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(0, $results['nbHits']);
    }

    public function testDeleteObjectByQuery()
    {
        $res = $this->index->addObjects([
            ['firstname' => 'Robin', 'objectID' => 'à/go/?à'],
            ['firstname' => 'Robert', 'objectID' => 'à/go/?à2'],
            ['firstname' => 'Robert', 'objectID' => 'à/go/?à3']
        ]);
        $this->index->waitTask($res['taskID']);
        $deletedCount = $this->index->deleteByQuery('Robert');

        $this->assertEquals(2, $deletedCount);

        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
    }

    public function testMultipleQueries()
    {
        $res = $this->index->addObject(['firstname' => 'Robin']);
        $this->index->waitTask($res['taskID']);
        $results = $this->client->multipleQueries([['indexName' => $this->safe_name('àlgol?à-php'), 'query' => '']]);
        $this->assertEquals(1, $results['results'][0]['nbHits']);
        $this->assertEquals('Robin', $results['results'][0]['hits'][0]['firstname']);
    }

    public function testDisjunctiveFaceting()
    {
        $this->index->setSettings(['attributesForFacetting' => ['city', 'stars', 'facilities']]);
        $task = $this->index->addObjects([
            ['name' => 'Hotel A', 'stars' => '*', 'facilities' => ['wifi', 'batch', 'spa'], 'city' => 'Paris'],
            ['name' => 'Hotel B', 'stars' => '*', 'facilities' => ['wifi'], 'city' => 'Paris'],
            ['name' => 'Hotel C', 'stars' => '**', 'facilities' => ['batch'], 'city' => 'San Francisco'],
            ['name' => 'Hotel D', 'stars' => '****', 'facilities' => ['spa'], 'city' => 'Paris'],
            ['name' => 'Hotel E', 'stars' => '****', 'facilities' => ['spa'], 'city' => 'New York']]
        );
        $this->index->waitTask($task['taskID']);

        $answer = $this->index->searchDisjunctiveFaceting('h', ['stars', 'facilities'], ['facets' => 'city']);
        $this->assertEquals(5, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));

        $answer = $this->index->searchDisjunctiveFaceting('h', ['stars', 'facilities'], ['facets' => 'city'], ['stars' => ['*']]);
        $this->assertEquals(2, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['**']);
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['****']);

        $answer = $this->index->searchDisjunctiveFaceting('h', ['stars', 'facilities'], ['facets' => 'city'], ['stars' => ['*'], 'city' => ['Paris']]);
        $this->assertEquals(2, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['****']);

        $answer = $this->index->searchDisjunctiveFaceting('h', ['stars', 'facilities'], ['facets' => 'city'], ['stars' => ['*', '****'], 'city' => ['Paris']]);
        $this->assertEquals(3, $answer['nbHits']);
        $this->assertEquals(1, count($answer['facets']));
        $this->assertEquals(2, count($answer['disjunctiveFacets']));
        $this->assertEquals(2, $answer['disjunctiveFacets']['stars']['*']);
        $this->assertEquals(1, $answer['disjunctiveFacets']['stars']['****']);
    }
}

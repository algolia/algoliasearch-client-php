<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class GetTest extends AlgoliaSearchTestCase
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
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testGetObject()
    {
        $res = $this->index->addObject(array('firstname' => 'Robin'), 'à/go/?à');
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $obj = $this->index->getObject('à/go/?à');
        $this->assertEquals('Robin', $obj['firstname']);
    }

    public function testGetObjects()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'Robin'),
            array('firstname' => 'Robert')
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
        $obj1 = $this->index->getObject($results['hits'][0]['objectID']);
        $obj2 = $this->index->getObject($results['hits'][1]['objectID'], 'firstname');
        $this->assertEquals($results['hits'][0]['firstname'], $obj1['firstname']);
        $this->assertEquals($results['hits'][1]['firstname'], $obj2['firstname']);
    }

    public function testGetSaveObjects()
    {
        $res = $this->index->saveObjects(array(
            array('firstname' => 'Oneil', 'objectID' => 'à/go/?à'),
            array('firstname' => 'Robert', 'objectID' => 'à/go/?à2')
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(1, $results['nbHits']);
        $obj = $this->index->getObject('à/go/?à');
        $this->assertEquals('Oneil', $obj['firstname']);
    }
}

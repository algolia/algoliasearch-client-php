<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class GetTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
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
        $res = $this->index->addObject(array(
            'firstname' => 'Robin',
            'lastname' => 'Lee'), 'à/go/?à');
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('');

        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);

        $obj = $this->index->getObject('à/go/?à');
        $this->assertArrayHasKey('firstname', $obj);
        $this->assertArrayHasKey('lastname', $obj);
        $this->assertEquals('Robin', $obj['firstname']);

        $obj = $this->index->getObject('à/go/?à', 'firstname');
        $this->assertArrayHasKey('firstname', $obj);
        $this->assertArrayNotHasKey('lastname', $obj);

        $obj = $this->index->getObject('à/go/?à', array('firstname'));
        $this->assertArrayHasKey('firstname', $obj);
        $this->assertArrayNotHasKey('lastname', $obj);

        $obj = $this->index->getObject('à/go/?à', '');
        $this->assertArrayHasKey('objectID', $obj);
        $this->assertArrayNotHasKey('firstname', $obj);
        $this->assertArrayNotHasKey('lastname', $obj);

        $obj = $this->index->getObject('à/go/?à', array());
        $this->assertArrayHasKey('objectID', $obj);
        $this->assertArrayNotHasKey('firstname', $obj);
        $this->assertArrayNotHasKey('lastname', $obj);
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

    public function testGetObjectsWithAttributesToRetrieve()
    {
        $res = $this->index->addObjects(array(
            array('firstname' => 'Robin', 'lastname' => 'Lee'),
            array('firstname' => 'Robert', 'lastname' => 'Watson')
        ));

        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);

        $objects1 = $this->index->getObjects(array($results['hits'][0]['objectID'], $results['hits'][1]['objectID']));
        $objects2 = $this->index->getObjects(array($results['hits'][0]['objectID'], $results['hits'][1]['objectID']), 'lastname');
        $objects3 = $this->index->getObjects(array($results['hits'][0]['objectID'], $results['hits'][1]['objectID']), array('lastname'));
        $objects4 = $this->index->getObjects(array($results['hits'][0]['objectID'], $results['hits'][1]['objectID']), '');
        $objects5 = $this->index->getObjects(array($results['hits'][0]['objectID'], $results['hits'][1]['objectID']), array());

        $this->assertCount(2, $objects1['results']);
        $this->assertCount(2, $objects2['results']);
        $this->assertCount(2, $objects3['results']);
        $this->assertCount(2, $objects4['results']);
        $this->assertCount(2, $objects5['results']);

        $firstResult = reset($objects1['results']);
        $this->assertArrayHasKey('objectID', $firstResult);
        $this->assertArrayHasKey('firstname', $firstResult);
        $this->assertArrayHasKey('lastname', $firstResult);

        $secondResult = reset($objects2['results']);
        $this->assertArrayHasKey('objectID', $secondResult);
        $this->assertArrayNotHasKey('firstname', $secondResult);
        $this->assertArrayHasKey('lastname', $secondResult);

        $thirdResult = reset($objects3['results']);
        $this->assertArrayHasKey('objectID', $thirdResult);
        $this->assertArrayNotHasKey('firstname', $thirdResult);
        $this->assertArrayHasKey('lastname', $thirdResult);

        $fourthResult = reset($objects4['results']);
        $this->assertArrayHasKey('objectID', $fourthResult);
        $this->assertArrayHasKey('firstname', $fourthResult);
        $this->assertArrayHasKey('lastname', $fourthResult);

        $fifthResult = reset($objects5['results']);
        $this->assertArrayHasKey('objectID', $fifthResult);
        $this->assertArrayHasKey('firstname', $fifthResult);
        $this->assertArrayHasKey('lastname', $fifthResult);
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

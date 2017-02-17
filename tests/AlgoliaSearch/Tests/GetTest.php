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
        $this->assertTrue(isset($obj['firstname']));
        $this->assertTrue(isset($obj['lastname']));
        $this->assertEquals('Robin', $obj['firstname']);

        $obj = $this->index->getObject('à/go/?à', 'firstname');
        $this->assertTrue(isset($obj['firstname']));
        $this->assertFalse(isset($obj['lastname']));

        $obj = $this->index->getObject('à/go/?à', array('firstname'));
        $this->assertTrue(isset($obj['firstname']));
        $this->assertFalse(isset($obj['lastname']));

        $obj = $this->index->getObject('à/go/?à', '');
        $this->assertTrue(isset($obj['objectID']));
        $this->assertFalse(isset($obj['firstname']));
        $this->assertFalse(isset($obj['lastname']));

        $obj = $this->index->getObject('à/go/?à', array());
        $this->assertTrue(isset($obj['objectID']));
        $this->assertFalse(isset($obj['firstname']));
        $this->assertFalse(isset($obj['lastname']));
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

        $this->assertEquals(2, count($objects1['results']));
        $this->assertEquals(2, count($objects2['results']));
        $this->assertEquals(2, count($objects3['results']));
        $this->assertEquals(2, count($objects4['results']));
        $this->assertEquals(2, count($objects5['results']));

        $firstResult = reset($objects1['results']);
        $this->assertTrue(isset($firstResult['objectID']));
        $this->assertTrue(isset($firstResult['firstname']));
        $this->assertTrue(isset($firstResult['lastname']));

        $secondResult = reset($objects2['results']);
        $this->assertTrue(isset($secondResult['objectID']));
        $this->assertFalse(isset($secondResult['firstname']));
        $this->assertTrue(isset($secondResult['lastname']));

        $thirdResult = reset($objects3['results']);
        $this->assertTrue(isset($thirdResult['objectID']));
        $this->assertFalse(isset($thirdResult['firstname']));
        $this->assertTrue(isset($thirdResult['lastname']));

        $fourthResult = reset($objects4['results']);
        $this->assertTrue(isset($fourthResult['objectID']));
        $this->assertTrue(isset($fourthResult['firstname']));
        $this->assertTrue(isset($fourthResult['lastname']));

        $fifthResult = reset($objects5['results']);
        $this->assertTrue(isset($fifthResult['objectID']));
        $this->assertTrue(isset($fifthResult['firstname']));
        $this->assertTrue(isset($fifthResult['lastname']));
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

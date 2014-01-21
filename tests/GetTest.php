<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class GetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('GetTest'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function testGetObject()
    {
        $res = $this->index->addObject(array("firstname" => "Robin"), "42");
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $obj = $this->index->getObject("42");
        $this->assertEquals('Robin', $obj['firstname']);
    }

    public function testGetObjects()
    {
        $res = $this->index->addObjects(array(
            array("firstname" => "Robin"),
            array("firstname" => "Robert")
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
        $obj1 = $this->index->getObject($results['hits'][0]['objectID']);
        $obj2 = $this->index->getObject($results['hits'][1]['objectID'], "firstname");
        $this->assertEquals($results['hits'][0]['firstname'], $obj1['firstname']);
        $this->assertEquals($results['hits'][1]['firstname'], $obj2['firstname']);
    }

    public function testGetSaveObjects()
    {
        $res = $this->index->saveObjects(array(
            array("firstname" => "Oneil", "objectID" => 1),
            array("firstname" => "Robert", "objectID" => 2)
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(1, $results['nbHits']);
        $obj = $this->index->getObject("1");
        $this->assertEquals('Oneil', $obj['firstname']);
    }

    private $client;
    private $index;
}

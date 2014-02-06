<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class DeleteTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function tearDown()
    {
        try {
            $this->client->deleteIndex(safe_name('àlgol?à-php'));          
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }

    }

    /**
     *  @expectedException Exception
     */
    public function testDeleteObject()
    {
        $res = $this->index->addObject(array("firstname" => "Robin", "objectID" => "à/go/?à"));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('', array('attributesToRetrieve' => array("firstname")));
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $del = $this->index->deleteObject($results['hits'][0]['objectID']);
        $this->index->waitTask($del['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(0, $results['nbHits']);

        $this->setExpectedException('Exception');
        $this->index->deleteObject(null);
    }

    private $client;
    private $index;
}

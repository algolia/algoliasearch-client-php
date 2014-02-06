<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class URLEncodeTest extends PHPUnit_Framework_TestCase
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

    public function testURLEncode()
    {
        $res = $this->index->addObject(array("firstname" => "Robin"), "a/go/?a");
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $obj = $this->index->getObject("a/go/?a");
        $this->assertEquals('Robin', $obj['firstname']);
        $res = $this->index->saveObject(array("firstname" => "Roger", "objectID" => "a/go/?a"));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Roger', $results['hits'][0]['firstname']);
        $res = $this->index->partialUpdateObject(array("firstname" => "Rodrigo", "objectID" => "a/go/?a"));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Rodrigo', $results['hits'][0]['firstname']);

    }
    private $client;
    private $index;
}

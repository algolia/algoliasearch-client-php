<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';

class BatchTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('BatchTest'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function testBatchCustomObjects()
    {
        $res = $this->index->batch(array(
            "requests" => array(
                array("action" => "addObject", "body" => array("firstname" => "Jimmie", "lastname" => "Barninger")),
                array("action" => "addObject", "body" => array("firstname" => "Oneil", "lastname" => "Barney")),
                array("action" => "updateObject", "objectID" => "3", "body" => array("firstname" => "Rob")),
                )
            )
        );
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);
    }


    private $client;
    private $index;
}

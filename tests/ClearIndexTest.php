<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class ClearIndexTest extends PHPUnit_Framework_TestCase
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

    public function testClearIndex()
    {
        $task = $this->index->addObject(array("firstname" => "Robin"));
        $this->index->waitTask($task['taskID']);

        $task = $this->index->clearIndex();
        $this->index->waitTask($task['taskID']);  

        $res = $this->index->search('');

        $this->assertEquals(0, $res['nbHits']);
    }

    private $client;
    private $index;
}

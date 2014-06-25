<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class ClearIndexTest extends AlgoliaTestCase
{
    private $client;
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaException $e) {
            // not fatal
        }  
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex(safe_name('àlgol?à-php'));           
        } catch (AlgoliaException $e) {
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
}

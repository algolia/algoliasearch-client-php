<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class BatchTest extends AlgoliaTestCase
{
    private $client;
    private $index;
    
    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
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

    public function testBatchCustomObjects()
    {
        $res = $this->index->batch(array(
            "requests" => array(
                array("action" => "addObject", "body" => array("firstname" => "Jimmie", "lastname" => "Barninger")),
                array("action" => "addObject", "body" => array("firstname" => "Oneil", "lastname" => "Barney")),
                array("action" => "updateObject", "objectID" => "à/go/?à", "body" => array("firstname" => "Rob")),
                )
            )
        );
        $this->index->waitTask($res['taskID'], 0.1);

        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);
    }
}

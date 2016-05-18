<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class ListIndexesTest extends AlgoliaSearchTestCase
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
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à2-php'));
        } catch (AlgoliaException $e) {
            // ListTest2 does not exist
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
        try {
            $this->client->deleteIndex($this->safe_name('ListTest2'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testListIndexes()
    {
        $this->index2 = $this->client->initIndex($this->safe_name('ListTest2'));
        $task = $this->index2->addObject(array('firstname' => 'Robin'));
        $this->index2->waitTask($task['taskID']);
        $resAfter = $this->client->listIndexes();

        $this->assertTrue($this->containsValue($resAfter['items'], 'name', $this->safe_name('ListTest2')));
    }
}

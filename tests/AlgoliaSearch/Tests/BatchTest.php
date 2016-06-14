<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class BatchTest extends AlgoliaSearchTestCase
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
        $res = $this->index->batch([
            'requests' => [
                    ['action' => 'addObject', 'body' => ['firstname' => 'Jimmie', 'lastname' => 'Barninger']],
                    ['action' => 'addObject', 'body' => ['firstname' => 'Oneil', 'lastname' => 'Barney']],
                    ['action' => 'updateObject', 'objectID' => 'à/go/?à', 'body' => ['firstname' => 'Rob']],
                ]
            ]
        );
        $this->index->waitTask($res['taskID'], 0.1);

        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);
    }

    public function testBatchCustomObjectsMultipleIndexes()
    {
        $res = $this->client->batch([
                ['action' => 'addObject', 'indexName' => $this->index->indexName, 'body' => ['firstname' => 'Jimmie', 'lastname' => 'Barninger']],
                ['action' => 'addObject', 'indexName' => $this->index->indexName, 'body' => ['firstname' => 'Oneil', 'lastname' => 'Barney']],
                ['action' => 'updateObject', 'indexName' => $this->index->indexName, 'objectID' => 'à/go/?à', 'body' => ['firstname' => 'Rob']],
            ]
        );
        $this->index->waitTask($res['taskID'][$this->index->indexName], 0.1);

        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);
    }
}

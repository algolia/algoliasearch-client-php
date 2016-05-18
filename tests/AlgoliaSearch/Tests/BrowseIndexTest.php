<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class BrowseIndexTest extends AlgoliaSearchTestCase
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

    public function testBrowseIndex()
    {
        $task = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($task['taskID']);
        $res = $this->index->search('');
        $this->assertEquals(1, $res['nbHits']);

        $res = $this->index->browse(0);
        $this->assertEquals(1, $res['nbHits']);
        $this->assertEquals('Robin', $res['hits'][0]['firstname']);
    }
}

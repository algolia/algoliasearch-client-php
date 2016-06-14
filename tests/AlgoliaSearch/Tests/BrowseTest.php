<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class BrowseTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
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
        $objects = array();

        for ($i = 0; $i < 1500; $i++) {
            $objects[] = array('objectID' => $i, 'i' => $i);
        }

        $task = $this->index->addObjects($objects);
        $this->index->waitTask($task['taskID']);

        $i = 0;

        foreach ($this->index->browse('') as $key => $value) {
            $i++;
        }

        $this->assertEquals(1500, $i);

        $i = 0;

        foreach ($this->index->browse('', array('numericFilters' => 'i<42')) as $key => $value) {
            $i++;
        }

        $this->assertEquals(42, $i);
    }

    public function testBrowseIndexWithSingleRecord()
    {
        $task = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($task['taskID']);

        $res = $this->index->search('');
        $this->assertEquals(1, $res['nbHits']);

        $data = $this->index->browse('');

        $data->valid();

        $record = $data->current();
        $this->assertEquals('Robin', $record['firstname']);
    }
}

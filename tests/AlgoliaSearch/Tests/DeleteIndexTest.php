<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class DeleteIndexTest extends AlgoliaSearchTestCase
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

    public function includeValue($tab, $attrName, $value)
    {
        foreach ($tab as $key => $elt) {
            if ($elt[$attrName] == $value) {
                return true;
            }
        }

        return false;
    }

    public function testDeleteIndex()
    {
        $this->index2 = $this->client->initIndex($this->safe_name('ListTest2'));
        $task = $this->index2->addObject(array('firstname' => 'Robin'));
        $this->index2->waitTask($task['taskID']);

        $res = $this->client->listIndexes();
        $this->assertTrue($this->includeValue($res['items'], 'name', $this->safe_name('ListTest2')));
        $task = $this->client->deleteIndex($this->safe_name('ListTest2'));
        $this->index2->waitTask($task['taskID']);

        $resAfter = $this->client->listIndexes();

        $this->assertFalse($this->includeValue($resAfter['items'], 'name', $this->safe_name('ListTest2')));
    }
}

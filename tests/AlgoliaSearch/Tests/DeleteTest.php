<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class DeleteTest extends AlgoliaSearchTestCase
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

    /**
     *  @expectedException Exception
     */
    public function testDeleteObject()
    {
        $res = $this->index->addObject(array('firstname' => 'Robin', 'objectID' => 'à/go/?à'));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('', array('attributesToRetrieve' => array('firstname')));
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
        $del = $this->index->deleteObject($results['hits'][0]['objectID']);
        $this->index->waitTask($del['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(0, $results['nbHits']);

        $this->setExpectedException('Exception');
        $this->index->deleteObject(null);
    }
}

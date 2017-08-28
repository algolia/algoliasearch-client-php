<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class IndexTest extends AlgoliaSearchTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Index
     */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        try {
            $res = $this->index->clearIndex();
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No method named unknown was found.
     */
    public function testShouldThrowAnExceptionIfUnknownMethodIsCalled()
    {
        $clientContextMock = $this->getMockBuilder('AlgoliaSearch\ClientContext')->disableOriginalConstructor()->getMock();
        $client = $this->getMockBuilder('AlgoliaSearch\Client')->disableOriginalConstructor()->getMock();
        $index = new Index($clientContextMock, $client, 'whatever');
        $index->unknown();
    }

    public function testShouldBeAbleToDeleteByNumericalFilter()
    {
        $res = $this->index->addObjects(array(
            array('custom_id' => 2),
            array('custom_id' => 2),
            array('custom_id' => 3),
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);

        $res = $this->index->deleteBy(array('filters' => 'custom_id = 2'));
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
    }
}

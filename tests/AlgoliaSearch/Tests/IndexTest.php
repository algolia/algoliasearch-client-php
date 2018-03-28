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

    public function testAddObjectsWithLegacySignature()
    {
        $res = $this->index->addObjects(
            array(array(
                'note' => 'this object should map `alpha` to the objectID',
                'alpha' => 'primary-key-1',
            )),
            'alpha'
        );
        $this->index->waitTask($res['taskID']);

        $res = $this->index->saveObjects(
            array(array(
                'note' => '`alpha` is the objectID',
                'alpha' => 'primary-key-2',
            )),
            'alpha'
        );
        $this->index->waitTask($res['taskID']);

        $res = $this->index->saveObject(
            array(
                'note' => '`alpha` is the objectID again',
                'alpha' => 'primary-key-3',
            ),
            'alpha'
        );
        $this->index->waitTask($res['taskID']);

        $results = $this->index->search('');
        $this->assertEquals(3, $results['nbHits']);

        foreach ($results['hits'] as $hit) {
            $this->assertEquals($hit['objectID'], $hit['alpha']);
        }

        $res = $this->index->partialUpdateObjects(
            array(array(
                'extra' => 'this object should have `extra` and `note` attributes',
                'alpha' => 'primary-key-1',
            )),
            'alpha'
        );
        $this->index->waitTask($res['taskID']);

        $objects = $this->index->getObject('primary-key-1');
        $this->assertArrayHasKey('note', $objects);
    }
}

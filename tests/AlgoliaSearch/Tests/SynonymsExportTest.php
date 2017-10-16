<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;
use AlgoliaSearch\Iterators\SynonymIterator;

class SynonymsExportTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
    private $index;

    private $indexName = 'test-synonym-export-php';

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->indexName);
        $this->index->addObject(array('note' => 'Create index in Algolia'));

        try {
            $res = $this->index->clearSynonyms();
            $this->index->waitTask($res['taskID'], 0.1);
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->indexName);
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldRejectInvalidHitsPerPage()
    {
        new SynonymIterator($this->index, 0);
    }

    public function testCanGetCurrentSynonymOfNewIterator()
    {
        $res = $this->index->saveSynonym('city',
            array(
                'type'     => 'synonym',
                'synonyms' => array('San Francisco', 'SF'),
            )
        );
        $this->index->waitTask($res['taskID'], 0.1);


        $synonym = $this->index->initSynonymIterator()->current();
        $this->assertEquals(array(
            'objectID' => 'city',
            'type'     => 'synonym',
            'synonyms' => array('San Francisco', 'SF'),
        ), $synonym);
    }

    public function testSynonymsExport()
    {
        $res = $this->index->batchSynonyms($this->getFakeSynonyms());
        $this->index->waitTask($res['taskID'], 0.1);

        $exported = array();
        $iterator = $this->index->initSynonymIterator(2);

        $i = 0;
        foreach ($iterator as $key => $synonym) {
            $this->assertArrayNotHasKey('_highlightResult', $synonym);
            $this->assertEquals($i++, $key);

            $exported[] = $synonym;
        }

        $this->assertCount(3, $exported);
    }

    public function testFoundSynonymsCanBeBatched()
    {
        $res = $this->index->batchSynonyms($this->getFakeSynonyms());
        $this->index->waitTask($res['taskID'], 0.1);


        $iterator = $this->index->initSynonymIterator();

        $synonyms = array();
        foreach ($iterator as $key => $synonym) {
            $synonyms[] = $synonym;
        }

        $res = $this->index->clearSynonyms();
        $this->index->waitTask($res['taskID']);

        $this->index->batchSynonyms($synonyms);
    }

    /**
     * @return array
     */
    private function getFakeSynonyms() {
        return array(
            array(
                'objectID' => 'city',
                'type'     => 'synonym',
                'synonyms' => array('San Francisco', 'SF'),
            ),
            array(
                'objectID'    => 'street',
                'type'        => 'altCorrection1',
                'word'        => 'Street',
                'corrections' => array('St')
            ),
            array(
                'objectID'    => 'avenue',
                'type'        => 'altCorrection1',
                'word'        => 'Avenue',
                'corrections' => array('Av', 'Ave')
            ),
        );
    }

}

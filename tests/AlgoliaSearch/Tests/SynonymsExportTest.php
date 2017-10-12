<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

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
            $this->clearSynonyms();
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

    public function testSynonymsExport()
    {
        $res = $this->index->batchSynonyms(array(
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
        ));

        $this->index->waitTask($res['taskID'], 0.1);
        $i = 0;
        $exported = array();

        $browser = $this->index->initSynonymBrowser(2);
        $lastObjectID = '';

        foreach ($browser as $k => $synonym) {
            // Check if the key is correct, not related to pagination
            $this->assertEquals($i, $k, 'The synonymsExporter returned incorrect keys.');
            $this->assertArrayHasKey('objectID', $synonym, 'objectID is missing in exported synonym');
            $this->assertNotEquals($lastObjectID, $synonym['objectID']);

            $i++;
            $lastObjectID = $synonym['objectID'];
            $exported[] = $synonym;
        }

        $this->assertFalse(isset($synonym['_highlightResult']), 'Synonyms were not formatted properly.');
        $this->assertEquals(3, count($exported), 'Some synonyms were not exported.');

        $this->clearSynonyms();
        $this->index->batchSynonyms($exported);
    }

    public function testCanGetCurrentSynonymOfNewBrowser()
    {
        $res = $this->index->saveSynonym('city',
            array(
                'type'     => 'synonym',
                'synonyms' => array('San Francisco', 'SF'),
            )
        );
        $this->index->waitTask($res['taskID'], 0.1);


        $synonym = $this->index->initSynonymBrowser()->current();
        $this->assertEquals(array(
            'objectID' => 'city',
            'type'     => 'synonym',
            'synonyms' => array('San Francisco', 'SF'),
        ), $synonym);
    }

    private function clearSynonyms()
    {
        $res = $this->index->clearSynonyms();
        $this->index->waitTask($res['taskID'], 0.1);
    }
}

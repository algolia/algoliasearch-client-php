<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;
use AlgoliaSearch\SynonymType;

class SynonymsTest extends AlgoliaSearchTestCase
{
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

    public function testSynonyms()
    {
        $res = $this->index->addObject(array('name' => '589 Howard St., San Francisco'));
        $this->index->waitTask($res['taskID'], 0.1);

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
            )
        ));

        $this->index->waitTask($res['taskID'], 0.1);

        $res = $this->index->getSynonym('city');
        $this->assertEquals('city', $res['objectID']);

        $res = $this->index->search('Howard Street SF');
        $this->assertEquals(1, $res['nbHits']);

        $res = $this->index->deleteSynonym('street');
        $this->index->waitTask($res['taskID'], 0.1);
        $res = $this->index->searchSynonyms('', array(SynonymType::SYNONYM), 0, 5);
        $this->assertEquals(1, $res['nbHits']);

        $res = $this->index->saveSynonym('city', array(
            'type'     => 'synonym',
            'synonyms' => array('San Francisco', 'SF', 'silicon valley')
        ));
        $this->index->waitTask($res['taskID'], 0.1);
        $res = $this->index->search('silicon valley');
        $this->assertEquals(1, $res['nbHits']);

        $res = $this->index->clearSynonyms();
        $this->index->waitTask($res['taskID'], 0.1);
        $res = $this->index->searchSynonyms('', array(), 0, 5);
        $this->assertEquals(0, $res['nbHits']);
    }
}

<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class SettingsTest extends AlgoliaSearchTestCase
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
            $res = $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
            $this->index->waitTask($res['taskID']);

            $res = $this->client->deleteIndex($this->safe_name('àlgol?à-php-replica'));
            $index = $this->client->initIndex($this->safe_name('àlgol?à-php-replica'));
            $index->waitTask($res['taskID']);
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testSettingsIndex()
    {
        $res = $this->index->setSettings(array('attributesToRetrieve' => array('firstname'), 'hitsPerPage' => 50));
        $this->index->waitTask($res['taskID']);
        $settings = $this->index->getSettings();
        $this->assertEquals(count($settings['attributesToRetrieve']), 1);
        $this->assertEquals($settings['attributesToRetrieve'][0], 'firstname');
    }

    public function testSettingsIndexWithForwardToReplicas()
    {
        $res = $this->index->setSettings(array('replicas' => array($this->safe_name('àlgol?à-php-replica'))));
        $this->index->waitTask($res['taskID']);

        $res = $this->index->addObject(array('test' => 'test'));
        $this->index->waitTask($res['taskID']);

        $res = $this->index->setSettings(array('attributesToRetrieve' => array('firstname'), 'hitsPerPage' => 50), true);
        $this->index->waitTask($res['taskID']);

        $replicaIndex = $this->client->initIndex($this->safe_name('àlgol?à-php-replica'));
        $settings = $replicaIndex->getSettings();

        $this->assertEquals(count($settings['attributesToRetrieve']), 1);
        $this->assertEquals($settings['attributesToRetrieve'][0], 'firstname');
    }

    public function testSearchFacet()
    {
        $this->index->addObjects(array(
            array('firstname' => 'Robin'),
            array('firstname' => 'Robert')
        ));
        $res = $this->index->setSettings(array('attributesForFaceting' => array('firstname')));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob', array('facetFilters' => array('firstname:Robert')));
        $this->assertEquals(1, $results['nbHits']);
    }
}

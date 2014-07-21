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
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }

    }

    public function testSettingsIndex()
    {
        $res = $this->index->setSettings(array("attributesToRetrieve" => array("firstname"), "hitsPerPage" => 50));
        $this->index->waitTask($res['taskID']);
        $settings = $this->index->getSettings();
        $this->assertEquals(count($settings['attributesToRetrieve']), 1);
        $this->assertEquals($settings['attributesToRetrieve'][0], 'firstname');
    }
}

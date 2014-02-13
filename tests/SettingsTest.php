<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class SettingsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));  
        $this->index = $this->client->initIndex(safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function tearDown()
    {
        try {
            $this->client->deleteIndex(safe_name('àlgol?à-php'));
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }

    }

    public function testSettingsIndex()
    {
        $res = $this->index->setSettings(array("attributesToRetrieve" => array("firstname"), "hitsPerPage" => 50));
        $settings = $this->index->getSettings();
        $this->assertEquals(count($settings['attributesToRetrieve']), 1);
        $this->assertEquals($settings['attributesToRetrieve'][0], 'firstname');
    }

    private $client;
    private $index;
}

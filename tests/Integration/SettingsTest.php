<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Tests\TestCase;

class SettingsTest extends TestCase
{
    protected static $indexes = array();

    private $settings = array(
        'hitsPerPage' => 13,
        'minWordSizefor2Typos' => 7,
        'paginationLimitedTo' => 999,
    );

    /** @var \Algolia\AlgoliaSearch\Client */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = self::getClient();
    }

    public function testSettingsCanBeUpdatedAndRetrieved()
    {
        self::$indexes['main'] = $this->safeName('settings-mgmt');
        $index = $this->client->index(self::$indexes['main']);

        $index->setSettings($this->settings);

        $retrievedSettings = $index->getSettings();
        $this->assertArraySubset($this->settings, $retrievedSettings);
    }

    /**
     * @depends testSettingsCanBeUpdatedAndRetrieved
     */
    public function testSettingsWithReplicas()
    {
        self::$indexes['replica'] = $this->safeName('settings-mgmt_REPLICA');
        $index = $this->client->index(self::$indexes['main']);
        $replica = $this->client->index(self::$indexes['replica']);

        $settingsWithReplicas = array_merge($this->settings, array('replicas' => array(self::$indexes['replica'])));

        // Assert that settings are forwarded by default
        $index->setSettings($settingsWithReplicas);
        $retrievedSettings = $replica->getSettings();
        $this->assertArraySubset($this->settings, $retrievedSettings);

        // Assert that settings are forwarded by default
        $formula = array('customRanking' => array('asc(something)'));
        $index->setSettings($formula, array('forwaredToReplicas' => false));
        $retrievedSettings = $replica->getSettings();
        $this->assertEquals(null, $retrievedSettings['customRanking']);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

//        foreach (self::$indexes as $indexName) {
//            self::getClient()->deleteIndex($indexName);
//        }
    }
}

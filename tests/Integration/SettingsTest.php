<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class SettingsTest extends AlgoliaIntegrationTestCase
{
    private $settings = array(
        'hitsPerPage' => 13,
        'minWordSizefor2Typos' => 7,
        'paginationLimitedTo' => 999,
    );

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = $this->safeName('settings-mgmt');
        }
    }

    public function testSettingsCanBeUpdatedAndRetrieved()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->setSettings($this->settings);

        $retrievedSettings = $index->getSettings();
        $this->assertArraySubset($this->settings, $retrievedSettings);
    }

    /**
     * @depends testSettingsCanBeUpdatedAndRetrieved
     */
    public function testSettingsWithReplicas()
    {
        $replica1 = $this->safeName('settings-mgmt_REPLICA');
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $replica = static::getClient()->initIndex($replica1);

        $settingsWithReplicas = array_merge($this->settings, array('replicas' => array($replica1)));

        // Assert that settings are forwarded by default
        $index->setSettings($settingsWithReplicas);
        $retrievedSettings = $replica->getSettings();
        $this->assertArraySubset($this->settings, $retrievedSettings);

        // Assert that settings are forwarded by default
        $formula = array('customRanking' => array('asc(something)'));
        $index->setSettings($formula, array('forwardToReplicas' => false));
        $retrievedSettings = $replica->getSettings();
        $this->assertEquals(null, $retrievedSettings['customRanking']);
    }
}

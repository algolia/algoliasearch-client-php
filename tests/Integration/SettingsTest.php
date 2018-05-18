<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class SettingsTest extends AlgoliaIntegrationTestCase
{
    private $settings = array(
        'hitsPerPage' => 13,
        'minWordSizefor2Typos' => 7,
        'paginationLimitedTo' => 999,
    );

    public function testSettingsCanBeUpdatedAndRetrieved()
    {
        static::$indexes['main'] = $this->safeName('settings-mgmt');
        $index = static::getClient()->index(static::$indexes['main']);

        $index->setSettings($this->settings);

        $retrievedSettings = $index->getSettings();
        $this->assertArraySubset($this->settings, $retrievedSettings);
    }

    /**
     * @depends testSettingsCanBeUpdatedAndRetrieved
     */
    public function testSettingsWithReplicas()
    {
        $replicaName = $this->safeName('settings-mgmt_REPLICA');
        $index = static::getClient()->index(static::$indexes['main']);
        $replica = static::getClient()->index($replicaName);

        $settingsWithReplicas = array_merge($this->settings, array('replicas' => array($replicaName)));

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
}

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
        $replica1 = self::safeName('settings-mgmt_REPLICA');
        try {
            static::getClient()->deleteIndex($replica1);
        } catch (\Exception $e) {
            //
        }
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $replica = static::getClient()->initIndex($replica1);

        $settingsWithReplicas = array_merge($this->settings, array(
            'replicas' => array($replica1),
            'hitsPerPage' => 16,
            'minWordSizefor2Typos' => 9,
            'paginationLimitedTo' => 885,
        ));

        // Assert that settings are NOT forwarded by default
        $index->setSettings($settingsWithReplicas);
        $retrievedPrimarySettings = $index->getSettings();
        $retrievedReplicaSettings = $replica->getSettings();
        $this->assertNotEquals($retrievedPrimarySettings['hitsPerPage'], $retrievedReplicaSettings['hitsPerPage']);

        // If I set forwardToReplicas to true by default, it should work
        $index = self::newClient(array('defaultForwardToReplicas' => true))
            ->initIndex(static::$indexes['main']);
        $index->setSettings($settingsWithReplicas);
        $retrievedPrimarySettings = $index->getSettings();
        $retrievedReplicaSettings = $replica->getSettings();
        $this->assertEquals($retrievedPrimarySettings['hitsPerPage'], $retrievedReplicaSettings['hitsPerPage']);

        // If the new default is true, I can still override it
        $formula = array('customRanking' => array('asc(something)'));
        $index->setSettings($formula, array('forwardToReplicas' => false));
        $retrievedSettings = $replica->getSettings();
        $this->assertEquals(null, $retrievedSettings['customRanking']);
    }
}

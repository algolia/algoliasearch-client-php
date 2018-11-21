<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class KeysTest extends AlgoliaIntegrationTestCase
{
    protected $acl = array('search', 'analytics');

    protected $keyParams = array(
        'maxQueriesPerIPPerHour' => 12,
        'maxHitsPerQuery' => 2,
    );

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = self::safeName('keys-mgmt');
            static::getClient()->initIndex(static::$indexes['main'])->clearObjects();
        }
    }

    public function testApiKeys()
    {
        /** @var \Algolia\AlgoliaSearch\SearchClient $client */
        $client = static::getClient();

        $response = $client
            ->addApiKey($this->acl, array_merge($this->keyParams, array('validity' => 800)))
            ->wait();

        $key = $client->getApiKey($response['key']);

        try {
            $this->assertArraySubset($this->keyParams, $key);
        } catch (\Exception $e) {
            var_dump($this->keyParams, $key);
        }

        $client->deleteApiKey($key['value'])->wait();

        try {
            $key = $client->getApiKey($key['value']);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }
    }
}

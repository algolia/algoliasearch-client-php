<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class KeysTest extends AlgoliaIntegrationTestCase
{
    protected $keyParams = array(
        'acl' => array('search', 'analytics'),
        'maxQueriesPerIPPerHour' => 12,
        'maxHitsPerQuery' => 2,
    );

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = $this->safeName('keys-mgmt');
            static::getClient()->clearIndex(static::$indexes['main']);
        }
    }

    public function testApiKeys()
    {
        /** @var \Algolia\AlgoliaSearch\Client $client */
        $client = static::getClient();

        $response = $client
            ->addApiKey(array_merge($this->keyParams, array('validity' => 800)))
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

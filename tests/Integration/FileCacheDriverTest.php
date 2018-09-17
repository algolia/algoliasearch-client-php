<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Cache\FileCacheDriver;
use Algolia\AlgoliaSearch\Cache\NullCacheDriver;
use Algolia\AlgoliaSearch\Client;

class FileCacheDriverTest extends AlgoliaIntegrationTestCase
{
    private static $cacheDir;

    public static function setUpBeforeClass()
    {
        parent::setUp();

        self::$cacheDir = dirname(__DIR__).'/cache/';
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir);
        }

        Algolia::setCache(new FileCacheDriver(self::$cacheDir));
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Algolia::setCache(new NullCacheDriver);
    }

    public function testClusterHostsIsCached()
    {
        $client = Client::create();
        $clusterHosts = $this->getClusterHostFromClient($client)->reset();
        $readOriginal = $clusterHosts->read();
        $this->assertCount(4, $readOriginal);

        $clusterHosts->failed($readOriginal[0]);
        $clusterHosts->failed($readOriginal[1]);
        $readAfter2failed = $clusterHosts->read();

        unset($client);

        $client = Client::create();
        $clusterHosts = $this->getClusterHostFromClient($client);
        $readAfterReadingCache = $clusterHosts->read();

        $this->assertCount(2, $readAfterReadingCache);
        $this->assertEquals($this->hash($readAfter2failed), $this->hash($readAfterReadingCache));
    }

    public function getClusterHostFromClient($clientInstance)
    {
        $ref = new \ReflectionProperty('Algolia\AlgoliaSearch\Client', 'api');
        $ref->setAccessible(true);
        $apiWrapper = $ref->getValue($clientInstance);

        $ref = new \ReflectionProperty('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper', 'clusterHosts');
        $ref->setAccessible(true);

        return $ref->getValue($apiWrapper);
    }

    private function hash(array $hosts)
    {
        return sha1(implode('', $hosts));
    }
}

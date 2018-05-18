<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Tests\SyncClient;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class AlgoliaIntegrationTestCase extends PHPUitTestCase
{
    protected static $indexes = array();

    /** @var SyncClient */
    private static $client;

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        foreach (static::$indexes as $indexName) {
            static::getClient()->deleteIndex($indexName);
        }
    }

    public static function safeName($name)
    {
        return $name;
    }

    protected static function getClient()
    {
        if (! self::$client) {
            self::$client = self::newClient();
        }

        return self::$client;
    }

    protected static function newClient($config = array())
    {
        $config += array(
            'app-id' => getenv('ALGOLIA_APP_ID'),
            'key' => getenv('ALGOLIA_API_KEY'),
            'hosts' => array(),
        );

        return new SyncClient(
            Client::create($config['app-id'], $config['key'], $config['hosts'])
        );
    }
}

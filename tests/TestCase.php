<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Client;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class TestCase extends PHPUitTestCase
{
    /** @var Client */
    private static $client;

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

        return Client::create($config['app-id'], $config['key'], $config['hosts']);
    }
}

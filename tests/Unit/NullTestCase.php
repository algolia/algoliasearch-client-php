<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Tests\NullHttpClient;
use PHPUnit\Framework\TestCase;

class NullTestCase extends TestCase
{
    /** @var \Algolia\AlgoliaSearch\Client */
    protected static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Algolia::setHttpClient(function () {
            return new NullHttpClient();
        });
        static::$client = Client::create('id', 'key');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Algolia::resetHttpClient();
    }
}

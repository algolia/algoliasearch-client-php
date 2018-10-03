<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Tests\NullHttpClient;
use PHPUnit\Framework\TestCase;

class NullTestCase extends TestCase
{
    /** @var \Algolia\AlgoliaSearch\Client */
    protected static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        HttpClientFactory::set(function () {
            return new NullHttpClient();
        });
        static::$client = Client::create('id', 'key');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        HttpClientFactory::reset();
    }
}

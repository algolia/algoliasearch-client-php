<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\NullHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class NullTestCase extends TestCase
{
    /** @var \Algolia\AlgoliaSearch\SearchClient */
    protected static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Algolia::setHttpClient(new NullHttpClient());
        static::$client = SearchClient::create('id', 'key');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Algolia::resetHttpClient();
    }
}

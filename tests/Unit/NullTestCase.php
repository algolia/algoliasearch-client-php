<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\NullHttpClient;
use PHPUnit\Framework\TestCase;

class NullTestCase extends TestCase
{
    /** @var \Algolia\AlgoliaSearch\SearchClient */
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Algolia::setHttpClient(new NullHttpClient());
        static::$client = SearchClient::create('id', 'key');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        Algolia::resetHttpClient();
    }
}

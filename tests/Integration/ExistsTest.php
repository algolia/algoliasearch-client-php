<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchClient;

/**
 * @internal
 */
class ExistsTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();
        static::$indexes['main'] = self::safeName('exists');
    }

    public function testIndexNotExist()
    {
        $index = SearchClient::get()->initIndex(static::$indexes['main']);

        self::assertFalse($index->exists());
    }

    public function testIndexExists()
    {
        $index = SearchClient::get()->initIndex(static::$indexes['main']);

        $index
            ->saveObject(
                array(
                    'firstname' => 'Jimmie',
                ),
                array('autoGenerateObjectIDIfNotExist' => true)
            )
            ->wait();

        self::assertTrue($index->exists());
    }
}

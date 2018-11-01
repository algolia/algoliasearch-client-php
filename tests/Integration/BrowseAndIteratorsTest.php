<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class BrowseAndIteratorsTest extends AlgoliaIntegrationTestCase
{
    protected static $index;

    public static function setUpBeforeClass()
    {
        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = static::safeName('browse-and-iterators');
        }

        static::$index = static::getClient()->initIndex(static::$indexes['main']);
    }

    public function testBrowseObjects()
    {
        self::$index->saveObjects($this->airports);

        $iterator = self::$index->browseObjects(array('hitsPerPage' => 2));

        $i = 0;
        $total = count($this->airports);
        foreach ($iterator as $airport) {
            $i++;
            if ($i > $total) {
                break;
            }
        }

        $this->assertEquals($total, $i);
    }
}

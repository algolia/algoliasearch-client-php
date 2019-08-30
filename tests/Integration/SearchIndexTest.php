<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchIndex;

class SearchIndexTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = self::safeName('general-index-mgmt');
        }
    }

    public static function tearDownAfterClass()
    {
    }

    protected function tearDown()
    {
    }

    public function testFindObject()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $index->saveObjects($this->companies);

        $res = $index->search('Algolia');
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'nicolas-dessaigne'), 0);
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'julien-lemoine'), 1);
        $this->assertEquals(SearchIndex::getObjectPosition($res, ''), -1);

        try {
            $index->findObject(function() { return false; });
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }


        $found = $index->findObject(function() { return true; });
        $this->assertEquals($found['position'], 0);
        $this->assertEquals($found['page'], 0);

        $callback = function($obj) {
            return array_key_exists('company', $obj) && $obj['company'] === 'Apple';
        };

        try {
            $index->findObject($callback, array('query' => 'algolia'));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        try {
            $index->findObject($callback, array('query' => '', 'paginate' => false, 'hitsPerPage' => 5));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $obj = $index->findObject($callback, array('query' => '', 'paginate' => true, 'hitsPerPage' => 5));
        $this->assertEquals($obj['position'], 0);
        $this->assertEquals($obj['page'], 2);
    }

    public function testGetObjectPosition()
    {
        $objects = array(
            'hits' => array(
                array('objectID' => 'one', 'name' => 'test'),
                array('objectID' => 'two', 'name' => 'cool')
            )
        );
        $found = SearchIndex::getObjectPosition($objects, 'one');
        $this->assertEquals($found, 0);
    }
}

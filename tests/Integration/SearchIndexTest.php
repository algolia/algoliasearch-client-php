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

    public function testFindObject()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $index->saveObjects($this->companies);

        $res = $index->search('Algolia');
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'nicolas-dessaigne'), 0);
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'julien-lemoine'), 1);
        $this->assertEquals(SearchIndex::getObjectPosition($res, ''), -1);

        try {
            $index->findObject(function () { return false; });
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        $found = $index->findObject(function () { return true; });
        $this->assertEquals($found['position'], 0);
        $this->assertEquals($found['page'], 0);

        $callback = function ($obj) {
            return array_key_exists('company', $obj) && 'Apple' === $obj['company'];
        };

        try {
            $index->findObject($callback, array('query' => 'algolia'));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        try {
            $index->findObject($callback, array('query' => '', 'paginate' => false, 'hitsPerPage' => 5));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        $obj = $index->findObject($callback, array('query' => '', 'paginate' => true, 'hitsPerPage' => 5));
        $this->assertEquals($obj['position'], 0);
        $this->assertEquals($obj['page'], 2);
    }
}

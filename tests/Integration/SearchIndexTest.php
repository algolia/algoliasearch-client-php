<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchIndex;

class SearchIndexTest extends AlgoliaIntegrationTestCase
{
    protected $index;

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = self::safeName('general-index-mgmt');
        }

        $this->index = static::getClient()->initIndex(static::$indexes['main']);
    }

    public function testIndexDoesNotExist()
    {
        self::assertFalse($this->index->exists());
    }

    public function testIndexExists()
    {
        $this->index
            ->saveObject(
                array(
                    'firstname' => 'Jimmie',
                ),
                array('autoGenerateObjectIDIfNotExist' => true)
            )
            ->wait();

        self::assertTrue($this->index->exists());
    }

    public function testFindObject()
    {
        $this->index->clearObjects();
        $this->index->saveObjects($this->companies);

        $res = $this->index->search('Algolia');
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'nicolas-dessaigne'), 0);
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'julien-lemoine'), 1);
        $this->assertEquals(SearchIndex::getObjectPosition($res, ''), -1);

        try {
            $this->index->findObject(function () { return false; });
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        $found = $this->index->findObject(function () { return true; });
        $this->assertEquals($found['position'], 0);
        $this->assertEquals($found['page'], 0);

        $callback = function ($obj) {
            return array_key_exists('company', $obj) && 'Apple' === $obj['company'];
        };

        try {
            $this->index->findObject($callback, array('query' => 'algolia'));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        try {
            $this->index->findObject($callback, array('query' => '', 'paginate' => false, 'hitsPerPage' => 5));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        $obj = $this->index->findObject($callback, array('query' => '', 'paginate' => true, 'hitsPerPage' => 5));
        $this->assertEquals($obj['position'], 0);
        $this->assertEquals($obj['page'], 2);
    }

    public function testSaveObjectsFails()
    {
        try {
            $this->index->saveObjects($this->companies[0]);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\InvalidArgumentObjectsException', $e);
        }
    }
}

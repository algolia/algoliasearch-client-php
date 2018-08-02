<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class IndexManagementTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = $this->safeName('general-index-mgmt');
        }
    }

    public function testListIndexes()
    {
        $client = static::getClient();
        $list = $client->listIndexes();
        $this->assertArrayHasKey('items', $list);
        $this->assertEquals(1, $list['nbPages']);

        $nbPages = ceil(count($list['items']) / 100);
        $list = $client->listIndexes(array('page' => 1));
        $this->assertEquals($nbPages, $list['nbPages']);
    }

    public function testCopyAndMoveIndex()
    {
        $client = static::getClient();
        $client->initIndex(static::$indexes['main'])->setSettings(
            array('hitsPerPage' => 31)
        );

        $copyName = static::$indexes['main'].'-COPY';
        $client->copyIndex(static::$indexes['main'], $copyName);
        $this->assertIndexExists($copyName);

        static::$indexes['moved'] = $copyName.'-MOVED';
        $client->copyIndex($copyName, static::$indexes['moved']);
        $this->assertIndexExists(static::$indexes['moved']);
    }

    public function testClearIndex()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $index->saveObjects($this->airports);

        $response = $index->search('');
        $this->assertGreaterThanOrEqual(count($this->airports), $response['nbHits']);
        static::getClient()->clearIndex(static::$indexes['main']);
        $response = $index->search('');
        $this->assertEquals(0, $response['nbHits']);
    }

    public function testDeleteIndex()
    {
        $client = static::getClient();
        $name = $this->safeName('index-to-be-delete-within-test-case');
        $client->initIndex($name)->setSettings(
            array('hitsPerPage' => 32)
        );

        $this->assertIndexExists($name);
        $client->deleteIndex($name);
        $this->assertIndexDoesNotExist($name);
    }

    private function assertIndexExists($indexName)
    {
        $list = static::getClient()->listIndexes();
        foreach ($list['items'] as $index) {
            if ($index['name'] === $indexName) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->assertTrue(false);
    }

    private function assertIndexDoesNotExist($indexName)
    {
        $list = static::getClient()->listIndexes();
        foreach ($list['items'] as $index) {
            if ($index['name'] === $indexName) {
                $this->assertTrue(false);
                return;
            }
        }

        $this->assertTrue(true);
    }
}

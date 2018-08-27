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
        $settings = array(
            'hitsPerPage' => 31,
            'userData' => 'API Client copy test',
        );
        $client = static::getClient();
        $mainIndex = $client->initIndex(static::$indexes['main']);
        $mainIndex->setSettings($settings);
        $mainIndex->saveObjects($this->airports);

        $copyIndexName = static::$indexes['main'].'-COPY';
        $client->copyIndex(static::$indexes['main'], $copyIndexName);
        $this->assertIndexExists($copyIndexName);
        $res = $client->initIndex($copyIndexName)->search('');
        $this->assertEquals(count($this->airports), $res['nbHits']);
        $set = $client->initIndex($copyIndexName)->getSettings();
        $this->assertArraySubset($settings, $set);

        static::$indexes['copy-scoped'] = $copyIndexName.'-scoped-settings';
        $client->copyIndex(static::$indexes['main'], static::$indexes['copy-scoped'], array('scope' => 'settings'));
        $this->assertIndexExists(static::$indexes['copy-scoped']);
        $res = $client->initIndex(static::$indexes['copy-scoped'])->search('');
        $this->assertEquals(0, $res['nbHits']);
        $set = $client->initIndex(static::$indexes['copy-scoped'])->getSettings();
        $this->assertArraySubset($settings, $set);

        static::$indexes['moved'] = $copyIndexName.'-MOVED';
        $client->moveIndex($copyIndexName, static::$indexes['moved']);
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

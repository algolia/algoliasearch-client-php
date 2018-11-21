<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class IndexManagementTest extends AlgoliaIntegrationTestCase
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

    public function testListIndexes()
    {
        $client = static::getClient();
        $list = $client->listIndices();
        $this->assertArrayHasKey('items', $list);
        $this->assertEquals(1, $list['nbPages']);

        $nbPages = ceil(count($list['items']) / 100);
        $list = $client->listIndices(array('page' => 1));
        $this->assertEquals($nbPages, $list['nbPages']);
    }

    public function testCopyAndMoveIndex()
    {
        $settings = array(
            'hitsPerPage' => 31,
            'userData' => 'API SearchClient copy test',
        );
        $client = static::getClient();
        $mainIndex = $client->initIndex(static::$indexes['main']);
        $mainIndex->setSettings($settings);
        $mainIndex->saveObjects($this->airports);

        $copyIndexName = static::$indexes['main'].'-COPY';
        $client->copyIndex(static::$indexes['main'], $copyIndexName);
        $this->assertIndexExists($copyIndexName);
        $copiedIndex = $client->initIndex($copyIndexName);
        $res = $copiedIndex->search('');
        $this->assertEquals(count($this->airports), $res['nbHits']);
        $set = $copiedIndex->getSettings();
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
        $index->clearObjects();
        $response = $index->search('');
        $this->assertEquals(0, $response['nbHits']);
    }

    public function testDeleteIndex()
    {
        $name = self::safeName('index-to-be-delete-within-test-case');
        $index = static::getClient()->initIndex($name);
        $index->setSettings(
            array('hitsPerPage' => 32)
        );

        $this->assertIndexExists($name);
        $index->delete();
        $this->assertIndexDoesNotExist($name);
    }

    private function assertIndexExists($indexName)
    {
        $list = static::getClient()->listIndices();
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
        $list = static::getClient()->listIndices();
        foreach ($list['items'] as $index) {
            if ($index['name'] === $indexName) {
                $this->assertTrue(false);

                return;
            }
        }

        $this->assertTrue(true);
    }
}

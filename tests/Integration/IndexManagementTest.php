<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class IndexManagementTest extends AlgoliaIntegrationTestCase
{
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
}

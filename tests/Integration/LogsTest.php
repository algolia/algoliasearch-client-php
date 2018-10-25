<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class LogsTest extends AlgoliaIntegrationTestCase
{
    public function testLogs()
    {
        /** @var \Algolia\AlgoliaSearch\SearchClient $client */
        $client = static ::getClient();

        $logs = $client->getLogs(array('length' => 12));

        $this->assertArrayHasKey('logs', $logs);
        $this->assertCount(12, $logs['logs']);
    }
}

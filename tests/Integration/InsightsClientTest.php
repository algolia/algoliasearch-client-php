<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\InsightsClient;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class InsightsClientTest extends TestCase
{
    public function testUserInsights()
    {
        $u = InsightsClient::create()->user('userTokenForTest');

        $this->assertEvent(
            $u->clickedFilters('eventName', 'indexName', 'filters:convertedToArray')
        );
        $this->assertEvent(
            $u->clickedObjectIDs('eventName', 'indexName', 'objectIDs')
        );
        $this->assertEvent(
            $u->clickedObjectIDsAfterSearch('eventName', 'indexName', 'objID', 2, md5('queryID'))
        );

        $this->assertEvent(
            $u->convertedFilters('eventName', 'indexName', 'filters:convertedToArray')
        );
        $this->assertEvent(
            $u->convertedObjectIDs('eventName', 'indexName', 'objectIDs')
        );
        $this->assertEvent(
            $u->convertedObjectIDsAfterSearch('eventName', 'indexName', 'objID', md5('queryID'))
        );

        $this->assertEvent(
            $u->viewedFilters('eventName', 'indexName', 'filters:convertedToArray')
        );
        $this->assertEvent(
            $u->viewedObjectIDs('eventName', 'indexName', 'objectIDs')
        );
    }

    private function assertEvent($response)
    {
        $this->assertArraySubset(array('status' => 200), $response);
    }
}

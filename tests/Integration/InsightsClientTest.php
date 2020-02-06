<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\InsightsClient;
use Algolia\AlgoliaSearch\SearchClient;

class InsightsClientTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();
        static::$indexes['insights'] = self::safeName('insights');
    }

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

    public function testSendEvent()
    {
        $two_days_ago_ms = (time() - (2 * 24 * 60 * 60)) * 1000;
        $insightClient = InsightsClient::create();
        $searchClient = SearchClient::create();
        $searchClient->initIndex(static::$indexes['insights'])->delete()->wait();
        $searchClient->initIndex(static::$indexes['insights'])->saveObject(array(
            'objectID' => 1,
        ))->wait();

        $response = $insightClient->sendEvent(array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['insights'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $two_days_ago_ms,
            )
        );

        $this->assertArraySubset(array('status' => 200), $response);
    }

    public function testSendEvents()
    {
        $two_days_ago_ms = (time() - (2 * 24 * 60 * 60)) * 1000;
        $insightClient = InsightsClient::create();
        $searchClient = SearchClient::create();
        $searchClient->initIndex(static::$indexes['insights'])->delete()->wait();
        $searchClient->initIndex(static::$indexes['insights'])->saveObject(array(
            'objectID' => 1,
        ))->wait();

        $response = $insightClient->sendEvents(array(array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['insights'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $two_days_ago_ms,
            ))
        );

        $this->assertArraySubset(array('status' => 200), $response);
    }

    private function assertEvent($response)
    {
        $this->assertArraySubset(array('status' => 200), $response);
    }
}

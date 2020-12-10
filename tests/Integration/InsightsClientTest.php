<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\InsightsClient;
use Algolia\AlgoliaSearch\SearchClient;

class InsightsClientTest extends AlgoliaIntegrationTestCase
{
    protected function setUp(): void
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
        $twoDaysAgoMs = (time() - (2 * 24 * 60 * 60)) * 1000;
        $insightsClient = InsightsClient::create();
        $searchClient = SearchClient::create();
        $searchClient->initIndex(static::$indexes['insights'])->saveObject(array(
            'objectID' => 1,
        ))->wait();

        $response = $insightsClient->sendEvent(array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['insights'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $twoDaysAgoMs,
            )
        );

        $this->assertEvent($response);
    }

    public function testSendEvents()
    {
        $twoDaysAgoMs = (time() - (2 * 24 * 60 * 60)) * 1000;
        $insightsClient = InsightsClient::create();
        $searchClient = SearchClient::create();
        $searchClient->initIndex(static::$indexes['insights'])->saveObject(array(
            'objectID' => 1,
        ))->wait();

        $response = $insightsClient->sendEvents(array(array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['insights'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $twoDaysAgoMs,
            ))
        );

        $this->assertEvent($response);
    }

    private function assertEvent($response)
    {
        $this->assertArraySubset(array(
            'message' => 'OK',
            'status' => 200,
        ), $response);
    }
}

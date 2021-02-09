<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\InsightsClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class InsightsClientTest extends BaseTest
{
    public function testInsightClient()
    {
        static::$indexes['sending_events'] = TestHelper::getTestIndexName('sending_events');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex(static::$indexes['sending_events']);

        /** @var InsightsClient $insightsClient */
        $insightsClient = InsightsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $objectOne = array('objectID' => 'one');
        $objectTwo = array('objectID' => 'two');

        $index->saveObjects(array($objectOne, $objectTwo))->wait();

        $twoDaysAgoMs = (time() - (2 * 24 * 60 * 60)) * 1000;

        $event = array(
            'eventType' => 'click',
            'eventName' => 'foo',
            'index' => static::$indexes['sending_events'],
            'userToken' => 'bar',
            'objectIDs' => array('one', 'two'),
            'timestamp' => $twoDaysAgoMs,
        );

        $response = $insightsClient->sendEvent($event);

        $events = array(
            array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['sending_events'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $twoDaysAgoMs,
            ),
            array(
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => static::$indexes['sending_events'],
                'userToken' => 'bar',
                'objectIDs' => array('one', 'two'),
                'timestamp' => $twoDaysAgoMs,
            ),
        );

        $insightsClient->sendEvents($events);

        // clicked_object_ids
        $insightUser = $insightsClient->user('bar');
        $response = $insightUser->clickedObjectIDs(
            'foo',
            static::$indexes['sending_events'],
            array('one', 'two')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        $insightUser = $insightsClient->user('bar');

        // clicked_object_ids_after_search
        $search = $index->search('', array('clickAnalytics' => true));
        $response = $insightUser->clickedObjectIDsAfterSearch(
            'foo',
            static::$indexes['sending_events'],
            array('one', 'two'),
            array(1, 2),
            $search['queryID']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // clicked_filters
        $response = $insightUser->clickedFilters(
            'foo',
            static::$indexes['sending_events'],
            array('filter:foo', 'filter:bar')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_object_ids
        $response = $insightUser->convertedObjectIDs(
            'foo',
            static::$indexes['sending_events'],
            array('one', 'two')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_object_ids_after_search
        $search = $index->search('', array('clickAnalytics' => true));
        $response = $insightUser->convertedObjectIDsAfterSearch(
            'foo',
            static::$indexes['sending_events'],
            array('one', 'two'),
            $search['queryID']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_filters
        $response = $insightUser->convertedFilters(
            'foo',
            static::$indexes['sending_events'],
            array('filter:foo', 'filter:bar')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // viewed_object_ids
        $response = $insightUser->viewedObjectIDs(
            'foo',
            static::$indexes['sending_events'],
            array('one', 'two')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // viewed_filters
        $response = $insightUser->viewedFilters(
            'foo',
            static::$indexes['sending_events'],
            array('filter:foo', 'filter:bar')
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);
    }
}

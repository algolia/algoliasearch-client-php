<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\InsightsClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class InsightsClientTest extends BaseTest
{
    public function testInsightClient()
    {
        $this->indexes['sending_events'] = TestHelper::getTestIndexName('sending_events');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex($this->indexes['sending_events']);

        /** @var InsightsClient $insightsClient */
        $insightsClient = InsightsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $objectOne = ['objectID' => 'one'];
        $objectTwo = ['objectID' => 'two'];

        $index->saveObjects([$objectOne, $objectTwo])->wait();

        $twoDaysAgoMs = (time() - (2 * 24 * 60 * 60)) * 1000;

        $event = [
            'eventType' => 'click',
            'eventName' => 'foo',
            'index' => $this->indexes['sending_events'],
            'userToken' => 'bar',
            'objectIDs' => ['one', 'two'],
            'timestamp' => $twoDaysAgoMs,
        ];

        $response = $insightsClient->sendEvent($event);

        $events = [
            [
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => $this->indexes['sending_events'],
                'userToken' => 'bar',
                'objectIDs' => ['one', 'two'],
                'timestamp' => $twoDaysAgoMs,
            ],
            [
                'eventType' => 'click',
                'eventName' => 'foo',
                'index' => $this->indexes['sending_events'],
                'userToken' => 'bar',
                'objectIDs' => ['one', 'two'],
                'timestamp' => $twoDaysAgoMs,
            ],
        ];

        $insightsClient->sendEvents($events);

        // clicked_object_ids
        $insightUser = $insightsClient->user('bar');
        $response = $insightUser->clickedObjectIDs(
            'foo',
            $this->indexes['sending_events'],
            ['one', 'two']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        $insightUser = $insightsClient->user('bar');

        // clicked_object_ids_after_search
        $search = $index->search('', ['clickAnalytics' => true]);
        $response = $insightUser->clickedObjectIDsAfterSearch(
            'foo',
            $this->indexes['sending_events'],
            ['one', 'two'],
            [1, 2],
            $search['queryID']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // clicked_filters
        $response = $insightUser->clickedFilters(
            'foo',
            $this->indexes['sending_events'],
            ['filter:foo', 'filter:bar']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_object_ids
        $response = $insightUser->convertedObjectIDs(
            'foo',
            $this->indexes['sending_events'],
            ['one', 'two']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_object_ids_after_search
        $search = $index->search('', ['clickAnalytics' => true]);
        $response = $insightUser->convertedObjectIDsAfterSearch(
            'foo',
            $this->indexes['sending_events'],
            ['one', 'two'],
            $search['queryID']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // converted_filters
        $response = $insightUser->convertedFilters(
            'foo',
            $this->indexes['sending_events'],
            ['filter:foo', 'filter:bar']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // viewed_object_ids
        $response = $insightUser->viewedObjectIDs(
            'foo',
            $this->indexes['sending_events'],
            ['one', 'two']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);

        // viewed_filters
        $response = $insightUser->viewedFilters(
            'foo',
            $this->indexes['sending_events'],
            ['filter:foo', 'filter:bar']
        );

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('OK', $response['message']);
    }
}

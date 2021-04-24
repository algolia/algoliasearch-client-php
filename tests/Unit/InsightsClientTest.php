<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\InsightsClient;

class InsightsClientTest extends RequestTestCase
{
    /** @var InsightsClient */
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client = InsightsClient::create('id', 'key', 'region');
    }

    public function testClick()
    {
        $expected = [
            'objectIDs' => [$objectID = 'objectID'],
            'eventType' => $type = 'click',
            'eventName' => $name = 'Name',
            'index' => $idx = 'index',
            'userToken' => $usrToken = 'token',
        ];

        try {
            self::$client->user($usrToken)->clickedObjectIDs($name, $idx, $objectID);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['positions'] = [$position = 12];
        $expected['queryID'] = $qID = 'queryID';

        try {
            self::$client->user($usrToken)->clickedObjectIDsAfterSearch($name, $idx, $objectID, $position, $qID);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['filters'] = [$filters = 'filters'];
        unset($expected['objectIDs'], $expected['queryID'], $expected['positions']);

        try {
            self::$client->user($usrToken)->clickedFilters($name, $idx, $filters);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }
    }

    public function testConversion()
    {
        $expected = [
            'objectIDs' => [$objectID = 'objectID'],
            'eventType' => $type = 'conversion',
            'eventName' => $name = 'Name',
            'index' => $idx = 'index',
            'userToken' => $usrToken = 'token',
        ];

        try {
            self::$client->user($usrToken)->convertedObjectIDs($name, $idx, $objectID);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['queryID'] = $qID = 'queryID';

        try {
            self::$client->user($usrToken)->convertedObjectIDsAfterSearch($name, $idx, $objectID, $qID);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['filters'] = [$filters = 'filters'];
        unset($expected['objectIDs'], $expected['queryID']);

        try {
            self::$client->user($usrToken)->convertedFilters($name, $idx, $filters);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }
    }

    public function testView()
    {
        $expected = [
            'objectIDs' => [$objectID = 'objectID'],
            'eventType' => $type = 'view',
            'eventName' => $name = 'Name',
            'index' => $idx = 'index',
            'userToken' => $usrToken = 'token',
        ];

        try {
            self::$client->user($usrToken)->viewedObjectIDs($name, $idx, $objectID);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['filters'] = [$filters = 'filters'];
        unset($expected['objectIDs']);

        try {
            self::$client->user($usrToken)->viewedFilters($name, $idx, $filters);
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }
    }

    private function assertRequest($expected, $e)
    {
        $requestBody = json_decode((string) $e->getRequest()->getBody(), true);
        $this->assertCount(1, $requestBody['events']);
        $this->assertEquals($expected, $requestBody['events'][0]);
    }
}

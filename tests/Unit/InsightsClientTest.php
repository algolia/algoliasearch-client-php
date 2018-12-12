<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\InsightsClient;

class InsightsClientTest extends RequestTestCase
{
    /** @var InsightsClient */
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$client = InsightsClient::create('id', 'key', 'region');
    }

    public function testClick()
    {
        $expected = array(
            "objectIDs" => array($objectID = "objectID",),
            "type" => $type = "click",
            "eventName" => $name = "Name",
            "index" => $idx = "index",
            "userToken" => $usrToken = "token",
            'timestamp' => $ts = floor(microtime(true) * 1000),
        );

        try {
            self::$client->user($usrToken)->clickedObjectIDs($name, $idx, $objectID, array('timestamp' => $ts));
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }

        $expected['queryID'] = $qID = 'queryID';
        $expected['positions'] = array($position = 12);

        try {
            self::$client->user($usrToken)->clickedObjectIDsAfterSearch($name, $idx, $objectID, $qID, $position, array('timestamp' => $ts));
        } catch (RequestException $e) {
            $this->assertRequest($expected, $e);
        }
    }

    private function assertRequest($expected, $e)
    {
        $requestBody = json_decode((string) $e->getRequest()->getBody(), true);

        $this->assertCount(1, $requestBody['events']);
        $this->assertArraySubset($expected, $requestBody['events'][0]);
    }
}

<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\SearchClient;
use DateTime;

class AnalyticsClientTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();

        static::$indexes['aa_testing'] = self::safeName('aa_testing');
    }

    public function testAATesting()
    {
        $analyticsClient = AnalyticsClient::create();
        $searchClient = SearchClient::create();

        $searchClient->initIndex(static::$indexes['aa_testing'])->delete()->wait();
        $searchClient->initIndex(static::$indexes['aa_testing'])->saveObject(array(
            'objectID' => 1,
        ))->wait();

        $dateTime = new DateTime('tomorrow');

        $response = $analyticsClient->addABTest(array(
            'name' => 'aaTestName',
            'variants' => array(
                array('index' => static::$indexes['aa_testing'], 'trafficPercentage' => 90),
                array(
                    'index' => static::$indexes['aa_testing'],
                    'trafficPercentage' => 10,
                    'customSearchParameters' => array('ignorePlurals' => true),
                ),
            ),
            'endAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
        ));

        $searchClient->waitTask(static::$indexes['aa_testing'], $response['taskID']);
        $abTest = $analyticsClient->getABTest($response['abTestID']);

        $this->assertSame($abTest['name'], 'aaTestName');
        $this->assertSame($abTest['status'], 'active');

        $response = $analyticsClient->deleteABTest($abTest['abTestID']);
        $searchClient->waitTask(static::$indexes['aa_testing'], $response['taskID']);
    }
}

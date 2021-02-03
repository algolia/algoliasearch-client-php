<?php

namespace Algolia\AlgoliaSearch\Tests\Cts\Integration;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\Cts\TestHelper;
use DateTime;

class AnalyticsClientTest extends BaseTest
{
    public function testAbTesting()
    {
        static::$indexes['ab_testing'] = TestHelper::getTestIndexName('ab_testing');
        static::$indexes['ab_testing_dev'] = TestHelper::getTestIndexName('ab_testing_dev');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex(static::$indexes['ab_testing']);

        /** @var SearchIndex $indexDev */
        $indexDev = TestHelper::getClient()->initIndex(static::$indexes['ab_testing_dev']);

        $responses = array();

        $object = array('objectID' => 'one');

        $responses[] = $index->saveObject($object, array('autoGenerateObjectIDIfNotExist' => true));
        $responses[] = $indexDev->saveObject($object, array('autoGenerateObjectIDIfNotExist' => true));

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $dateTime = new DateTime('tomorrow');

        $abTest = array(
            'name' => 'abTestName',
            'variants' => array(
                array(
                    'index' => static::$indexes['ab_testing'],
                    'trafficPercentage' => 60,
                    'description' => 'a description',
                ),
                array(
                    'index' => static::$indexes['ab_testing_dev'],
                    'trafficPercentage' => 40,
                ),
            ),
            'endAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
        );

        $analyticsClient = AnalyticsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $response = $analyticsClient->addABTest($abTest);
        $abTestId = $response['abTestID'];
        $index->waitTask($response['taskID']);

        $result = $analyticsClient->getABTest($abTestId);

        self::assertSame($abTest['name'], $result['name']);
        self::assertSame($abTest['endAt'], $result['endAt']);
        self::assertSame($abTest['variants'][0]['index'], $result['variants'][0]['index']);
        self::assertSame($abTest['variants'][0]['trafficPercentage'], $result['variants'][0]['trafficPercentage']);
        self::assertSame($abTest['variants'][0]['description'], $result['variants'][0]['description']);
        self::assertSame($abTest['variants'][1]['index'], $result['variants'][1]['index']);
        self::assertSame($abTest['variants'][1]['trafficPercentage'], $result['variants'][1]['trafficPercentage']);
        self::assertNotEquals('stopped', $result['status']);

        $results = $analyticsClient->getABTests();
        $found = false;

        foreach ($results['abtests'] as $fetchedAbTest) {
            if ($fetchedAbTest['name'] != $abTest['name']) {
                continue;
            }
            self::assertSame($abTest['name'], $fetchedAbTest['name']);
            self::assertSame($abTest['endAt'], $fetchedAbTest['endAt']);
            self::assertSame($abTest['variants'][0]['index'], $fetchedAbTest['variants'][0]['index']);
            self::assertSame(
                $abTest['variants'][0]['trafficPercentage'],
                $fetchedAbTest['variants'][0]['trafficPercentage']
            );
            self::assertSame($abTest['variants'][0]['description'], $fetchedAbTest['variants'][0]['description']);
            self::assertSame($abTest['variants'][1]['index'], $fetchedAbTest['variants'][1]['index']);
            self::assertSame(
                $abTest['variants'][1]['trafficPercentage'],
                $fetchedAbTest['variants'][1]['trafficPercentage']
            );
            self::assertNotEquals('stopped', $fetchedAbTest['status']);
            $found = true;
        }

        self::assertTrue($found);

        // @todo check stopABTest

        $response = $analyticsClient->deleteABTest($result['abTestID']);
        $index->waitTask($response['taskID']);

        try {
            $result = $analyticsClient->getABTest($abTestId);
        } catch (\Exception $e) {
            self::assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
            self::assertEquals(404, $e->getCode());
            self::assertEquals('ABTestID not found', $e->getMessage());
        }
    }

    public function testAaTesting()
    {
        static::$indexes['aa_testing'] = TestHelper::getTestIndexName('aa_testing');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex(static::$indexes['aa_testing']);

        $analyticsClient = AnalyticsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $object = array('objectID' => 'one');
        $res = $index->saveObject($object, array('autoGenerateObjectIDIfNotExist' => true))->wait();
        $dateTime = new DateTime('tomorrow');

        $aaTest = array(
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
        );

        $response = $analyticsClient->addABTest($aaTest);
        $aaTestId = $response['abTestID'];
        TestHelper::getClient()->waitTask(static::$indexes['aa_testing'], $response['taskID']);

        $fetchedAbTest = $analyticsClient->getABTest($aaTestId);

        self::assertSame($aaTest['name'], $fetchedAbTest['name']);
        self::assertSame($aaTest['endAt'], $fetchedAbTest['endAt']);
        self::assertSame($aaTest['variants'][0]['index'], $fetchedAbTest['variants'][0]['index']);
        self::assertSame(
            $aaTest['variants'][0]['trafficPercentage'],
            $fetchedAbTest['variants'][0]['trafficPercentage']
        );
        self::assertSame($aaTest['variants'][1]['index'], $fetchedAbTest['variants'][1]['index']);
        self::assertSame(
            $aaTest['variants'][1]['trafficPercentage'],
            $fetchedAbTest['variants'][1]['trafficPercentage']
        );
        self::assertSame(
            $aaTest['variants'][1]['customSearchParameters'],
            $fetchedAbTest['variants'][1]['customSearchParameters']
        );
        self::assertNotEquals('stopped', $fetchedAbTest['status']);

        $response = $analyticsClient->deleteABTest($aaTestId);
        $index->waitTask($response['taskID']);

        try {
            $result = $analyticsClient->getABTest($aaTestId);
        } catch (\Exception $e) {
            self::assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
            self::assertEquals(404, $e->getCode());
            self::assertEquals('ABTestID not found', $e->getMessage());
        }
    }
}

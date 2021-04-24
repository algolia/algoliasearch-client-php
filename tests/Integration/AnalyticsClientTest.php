<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;
use DateTime;

class AnalyticsClientTest extends BaseTest
{
    public function testAbTesting()
    {
        $this->indexes['ab_testing'] = TestHelper::getTestIndexName('ab_testing');
        $this->indexes['ab_testing_dev'] = TestHelper::getTestIndexName('ab_testing_dev');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex($this->indexes['ab_testing']);

        /** @var SearchIndex $indexDev */
        $indexDev = TestHelper::getClient()->initIndex($this->indexes['ab_testing_dev']);

        $responses = [];

        $object = ['objectID' => 'one'];

        $index->saveObject($object, ['autoGenerateObjectIDIfNotExist' => true])->wait();
        $indexDev->saveObject($object, ['autoGenerateObjectIDIfNotExist' => true])->wait();

        $dateTime = new DateTime('tomorrow');
        $abTestName = $this->indexes['ab_testing'];

        $abTest = [
            'name' => $abTestName,
            'variants' => [
                [
                    'index' => $this->indexes['ab_testing'],
                    'trafficPercentage' => 60,
                    'description' => 'a description',
                ],
                [
                    'index' => $this->indexes['ab_testing_dev'],
                    'trafficPercentage' => 40,
                ],
            ],
            'endAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
        ];

        $analyticsClient = AnalyticsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $cpt = 0;
        do {
            if ($cpt >= 10) {
                break;
            }
            $index->exists() && $indexDev->exists();
            sleep(1);
            $cpt++;
        } while (false);

        $response = TestHelper::retry(function () use ($analyticsClient, $abTest) {
            return $analyticsClient->addABTest($abTest);
        }, 0.1, 40);

        $abTestId = $response['abTestID'];
        $index->waitTask($response['taskID']);

        $result = $analyticsClient->getABTest($abTestId);

        $this->assertSame($abTest['name'], $result['name']);
        $this->assertSame($abTest['endAt'], $result['endAt']);
        $this->assertSame($abTest['variants'][0]['index'], $result['variants'][0]['index']);
        $this->assertSame($abTest['variants'][0]['trafficPercentage'], $result['variants'][0]['trafficPercentage']);
        $this->assertSame($abTest['variants'][0]['description'], $result['variants'][0]['description']);
        $this->assertSame($abTest['variants'][1]['index'], $result['variants'][1]['index']);
        $this->assertSame($abTest['variants'][1]['trafficPercentage'], $result['variants'][1]['trafficPercentage']);
        $this->assertNotEquals('stopped', $result['status']);

        $results = $analyticsClient->getABTests();
        $found = false;

        foreach ($results['abtests'] as $fetchedAbTest) {
            if ($fetchedAbTest['name'] != $abTest['name']) {
                continue;
            }
            $this->assertSame($abTest['name'], $fetchedAbTest['name']);
            $this->assertSame($abTest['endAt'], $fetchedAbTest['endAt']);
            $this->assertSame($abTest['variants'][0]['index'], $fetchedAbTest['variants'][0]['index']);
            $this->assertSame(
                $abTest['variants'][0]['trafficPercentage'],
                $fetchedAbTest['variants'][0]['trafficPercentage']
            );
            $this->assertSame($abTest['variants'][0]['description'], $fetchedAbTest['variants'][0]['description']);
            $this->assertSame($abTest['variants'][1]['index'], $fetchedAbTest['variants'][1]['index']);
            $this->assertSame(
                $abTest['variants'][1]['trafficPercentage'],
                $fetchedAbTest['variants'][1]['trafficPercentage']
            );
            $this->assertNotEquals('stopped', $fetchedAbTest['status']);
            $found = true;
        }

        $this->assertTrue($found);

        $response = $analyticsClient->stopABTest($abTestId);
        $index->waitTask($response['taskID']);

        $result = $analyticsClient->getABTest($abTestId);
        $this->assertEquals('stopped', $result['status']);

        $response = $analyticsClient->deleteABTest($abTestId);
        $index->waitTask($response['taskID']);

        try {
            $result = $analyticsClient->getABTest($abTestId);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
            $this->assertEquals(404, $e->getCode());
            $this->assertEquals('ABTestID not found', $e->getMessage());
        }
    }

    public function testAaTesting()
    {
        $this->indexes['aa_testing'] = TestHelper::getTestIndexName('aa_testing');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex($this->indexes['aa_testing']);

        $analyticsClient = AnalyticsClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $object = ['objectID' => 'one'];
        $res = $index->saveObject($object, ['autoGenerateObjectIDIfNotExist' => true])->wait();
        $dateTime = new DateTime('tomorrow');
        $abTestName = $this->indexes['aa_testing'];

        $aaTest = [
            'name' => $abTestName,
            'variants' => [
                ['index' => $this->indexes['aa_testing'], 'trafficPercentage' => 90],
                [
                    'index' => $this->indexes['aa_testing'],
                    'trafficPercentage' => 10,
                    'customSearchParameters' => ['ignorePlurals' => true],
                ],
            ],
            'endAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
        ];

        $cpt = 0;
        do {
            if ($cpt >= 10) {
                break;
            }
            $index->exists();
            sleep(1);
            $cpt++;
        } while (false);

        $response = TestHelper::retry(function () use ($analyticsClient, $aaTest) {
            return $analyticsClient->addABTest($aaTest);
        }, 0.1, 40);

        $aaTestId = $response['abTestID'];
        TestHelper::getClient()->waitTask($this->indexes['aa_testing'], $response['taskID']);

        $fetchedAbTest = $analyticsClient->getABTest($aaTestId);

        $this->assertSame($aaTest['name'], $fetchedAbTest['name']);
        $this->assertSame($aaTest['endAt'], $fetchedAbTest['endAt']);
        $this->assertSame($aaTest['variants'][0]['index'], $fetchedAbTest['variants'][0]['index']);
        $this->assertSame(
            $aaTest['variants'][0]['trafficPercentage'],
            $fetchedAbTest['variants'][0]['trafficPercentage']
        );
        $this->assertSame($aaTest['variants'][1]['index'], $fetchedAbTest['variants'][1]['index']);
        $this->assertSame(
            $aaTest['variants'][1]['trafficPercentage'],
            $fetchedAbTest['variants'][1]['trafficPercentage']
        );
        $this->assertSame(
            $aaTest['variants'][1]['customSearchParameters'],
            $fetchedAbTest['variants'][1]['customSearchParameters']
        );
        $this->assertNotEquals('stopped', $fetchedAbTest['status']);

        $response = $analyticsClient->deleteABTest($aaTestId);
        $index->waitTask($response['taskID']);

        try {
            $result = $analyticsClient->getABTest($aaTestId);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
            $this->assertEquals(404, $e->getCode());
            $this->assertEquals('ABTestID not found', $e->getMessage());
        }
    }
}

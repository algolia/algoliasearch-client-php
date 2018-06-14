<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class AnalyticsTest extends AlgoliaSearchTestCase
{
    /** @var \AlgoliaSearch\Analytics */
    private $analytics;
    private $indexName;

    protected function setUp()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->analytics = $client->initAnalytics();

        $this->indexName = $this->safe_name('àlgol?à-php-ABTest-tmp');
        $index = $client->initIndex($this->indexName);
        $res = $index->addObject(array('record' => 'I need this index'));
        $res = $index->setSettings(array('replicas' => array($this->indexName.'-alt')));
        $index->waitTask($res['taskID']);
    }

    public function testListABTests()
    {
        $this->analytics->getABTests(array('offset' => 1, 'limit' => 2));
        $abTests = $this->analytics->getABTests();

        $this->assertEquals(count((array) $abTests['abtests']), $abTests['count']);
    }

    public function testSingleABTestOperations()
    {
        $abTestToAdd = $this->getExampleABTest('Some test');

        $res = $this->analytics->addABTest($abTestToAdd);
        $this->analytics->waitTask($res['index'], $res['taskID']);
        $abTestID = $res['abTestID'];

        $abTest = $this->analytics->getABTest($abTestID);
        $this->assertEquals($abTest['abTestID'], $abTestID);
        $this->assertArraySubset($abTestToAdd['variants'][0], $abTest['variants'][0]);
        unset($abTestToAdd['variants']);

        unset($abTestToAdd['endAt']); // Because time is modified by the API
        $this->assertArraySubset($abTestToAdd, $abTest);

        $res = $this->analytics->stopABTest($abTestID);
        $this->analytics->waitTask($res['index'], $res['taskID']);
        $abTest = $this->analytics->getABTest($abTestID);
        $this->assertEquals($abTest['status'], 'stopped');

        $res = $this->analytics->deleteABTest($abTestID);
        $this->analytics->waitTask($res['index'], $res['taskID']);
        try {
            $abTest = $this->analytics->getABTest($abTestID);
            $this->assertTrue(false, "ABTest wasn't deleted properly.");
        } catch (AlgoliaException $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    /**
     * @dataProvider dataInvalidAbTest
     * @expectedException \AlgoliaSearch\AlgoliaException
     */
    public function testAddInvalidABTest($abTest)
    {
        $this->analytics->addABTest($abTest);
    }

    public function dataInvalidAbTest()
    {
        return array(
            array(array()),
            array(array('name' => 'invalid AB Test')),
            array(array('variants' => array())),
        );
    }

    public function getExampleABTest($name)
    {
        $dt = new \DateTime('tomorrow');
        return array(
            "name" => $name,
            "variants" => array(
                array("index" => $this->indexName,"trafficPercentage" => 90, "description" =>  ""),
                array("index" => $this->indexName."-alt","trafficPercentage" => 10),
            ),
            "endAt" =>  $dt->format('Y-m-d\TH:i:s\Z'),
        );
    }
}

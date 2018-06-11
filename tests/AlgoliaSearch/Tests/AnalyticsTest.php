<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class AnalyticsTest extends AlgoliaSearchTestCase
{
    /** @var \AlgoliaSearch\Analytics */
    private $analytics;

    protected function setUp()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->analytics = $client->initAnalytics();

        $this->indexName = $this->safe_name('àlgol?à-php-ABTest-tmp');
        $index = $client->initIndex($this->indexName);
        $res = $index->addObject(['record' => 'I need this index']);
        $res = $index->setSettings(['replicas' => [$this->indexName.'-alt']]);
        $index->waitTask($res['taskID']);
    }

    public function testListABTests()
    {
        $this->analytics->getABTests(array('offset' => 1, 'limit' => 2));
        $abTests = $this->analytics->getABTests();

        $this->assertEquals(count($abTests['abtests']), $abTests['count']);
    }

    public function testAddGetAndDeleteABTest()
    {
        $abTestToAdd = $this->getABTest('Some test');

        try {
            $response = $this->analytics->addABTest($abTestToAdd);
            $abTestID = $response['abtestID'];
        } catch (AlgoliaException $e) {
            $idToDelete = $this->guessABTestID($this->indexName);
            $this->analytics->deleteABTest($idToDelete);
            sleep(3);

            $response = $this->analytics->addABTest($abTestToAdd);
            $abTestID = $response['abtestID'];
        }

        sleep(2); // Just in case

        $abTest = $this->analytics->getABTest($abTestID);
        $this->assertEquals($abTest['abtestID'], $abTestID);
        $this->assertArraySubset($abTestToAdd['variants'][0], $abTest['variants'][0]);
        unset($abTestToAdd['variants']);

        unset($abTestToAdd['endAt']); // Because time is modified by the API
        $this->assertArraySubset($abTestToAdd, $abTest);
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

    public function getABTest($name)
    {
        return array(
            "name" => $name,
            "variants" => array(
                array("index" => $this->indexName,"trafficPercentage" => 90, "description" =>  ""),
                array("index" => $this->indexName."-alt","trafficPercentage" => 10),
            ),
            "endAt" =>  (new \DateTime('tomorrow'))->format('Y-m-d\TH:i:s\Z'),
        );
    }

    private function guessABTestID($indexName)
    {
        $list = $this->analytics->getABTests(array('limit' => 1000));
        foreach ($list['abtests'] as $ab) {
            if ($ab['variants'][0]['index'] == $indexName) {
                return $ab['abtestID'];
            }
        }

        return null; // this will fail later
    }
}

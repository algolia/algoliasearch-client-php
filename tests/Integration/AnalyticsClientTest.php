<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\Config\AnalyticsConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Tests\TestHelper;
use DateTime;
use Psr\Http\Message\RequestInterface;

class AnalyticsClientTest extends BaseTest implements HttpClientInterface
{
    /**
     * @var RequestInterface[]
     */
    private $recordedRequests = [];

    protected function assertRequests(array $requests)
    {
        $this->assertGreaterThan(0, count($requests));
        $this->assertEquals(count($requests), count($this->recordedRequests));

        foreach ($requests as $i => $request) {
            $recordedRequest = $this->recordedRequests[$i];

            $this->assertEquals($request['method'], $recordedRequest->getMethod());
            $this->assertEquals($request['path'], $recordedRequest->getUri()->getPath());
            $this->assertEquals($request['body'], $recordedRequest->getBody()->getContents());
        }
    }

    protected function getClient()
    {
        $api = new ApiWrapper($this, AnalyticsConfig::create(), ClusterHosts::create('127.0.0.1'));
        $config = AnalyticsConfig::create('foo', 'bar');

        return new AnalyticsClient($api, $config);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $this->recordedRequests[] = $request;

        return new Response(200, [], '{}');
    }

    public function testAbTesting()
    {
        $this->indexes['ab_testing'] = TestHelper::getTestIndexName('ab_testing');
        $this->indexes['ab_testing_dev'] = TestHelper::getTestIndexName('ab_testing_dev');
        $this->indexes['aa_testing'] = TestHelper::getTestIndexName('aa_testing');

        $dateTime = new DateTime('tomorrow');
        $abTestName = $this->indexes['ab_testing'];
        $aaTestName = $this->indexes['aa_testing'];
        $date = $dateTime->format('Y-m-d\TH:i:s\Z');

        $abTest = [
            'name' => $abTestName,
            'variants' => [
                [
                    'index' => $abTestName,
                    'trafficPercentage' => 60,
                    'description' => 'a description',
                ],
                [
                    'index' => $this->indexes['ab_testing_dev'],
                    'trafficPercentage' => 40,
                ],
            ],
            'endAt' => $date,
        ];

        $aaTest = [
            'name' => $aaTestName,
            'variants' => [
                ['index' => $aaTestName, 'trafficPercentage' => 90],
                [
                    'index' => $aaTestName,
                    'trafficPercentage' => 10,
                    'customSearchParameters' => ['ignorePlurals' => true],
                ],
            ],
            'endAt' => $date,
        ];

        $analyticsClient = $this->getClient();
        // Test AB Testing format
        $analyticsClient->addABTest($abTest);
        // Test AA Testing format
        $analyticsClient->addABTest($aaTest);

        $abTestId = 'myAbTestID';
        // Test Stop AB test
        $analyticsClient->stopABTest($abTestId);
        // Test get AB test
        $analyticsClient->getABTest($abTestId);
        // Test delete AB test
        $analyticsClient->deleteABTest($abTestId);

        $this->assertRequests([
            [
                'path' => '/2/abtests',
                'method' => 'POST',
                'body' => '{"name":"'.$abTestName.'","variants":[{"index":"'.$abTestName.'","trafficPercentage":60,"description":"a description"},{"index":"'.$this->indexes['ab_testing_dev'].'","trafficPercentage":40}],"endAt":"'.$date.'"}',
            ],
            [
                'path' => '/2/abtests',
                'method' => 'POST',
                'body' => '{"name":"'.$aaTestName.'","variants":[{"index":"'.$aaTestName.'","trafficPercentage":90},{"index":"'.$aaTestName.'","trafficPercentage":10,"customSearchParameters":{"ignorePlurals":true}}],"endAt":"'.$date.'"}',
            ],
            [
                'path' => '/2/abtests/myAbTestID/stop',
                'method' => 'POST',
                'body' => '',
            ],
            [
                'path' => '/2/abtests/myAbTestID',
                'method' => 'GET',
                'body' => '',
            ],
            [
                'path' => '/2/abtests/myAbTestID',
                'method' => 'DELETE',
                'body' => '',
            ],
        ]);
    }
}

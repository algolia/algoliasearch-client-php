<?php

namespace Algolia\AlgoliaSearch\Test\Api;

use Algolia\AlgoliaSearch\Api\AbtestingClient;
use Algolia\AlgoliaSearch\Configuration\AbtestingConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * AbtestingTest
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class AbtestingTest extends TestCase implements HttpClientInterface
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

            $this->assertEquals(
                $request['method'],
                $recordedRequest->getMethod()
            );

            $this->assertEquals(
                $request['path'],
                $recordedRequest->getUri()->getPath()
            );

            if (isset($request['body'])) {
                $this->assertEquals(
                    json_encode($request['body']),
                    $recordedRequest->getBody()->getContents()
                );
            }
        }
    }

    public function sendRequest(
        RequestInterface $request,
        $timeout,
        $connectTimeout
    ) {
        $this->recordedRequests[] = $request;

        return new Response(200, [], '{}');
    }

    protected function getClient()
    {
        $api = new ApiWrapper(
            $this,
            AbtestingConfig::create(
                getenv('ALGOLIA_APP_ID'),
                getenv('ALGOLIA_API_KEY')
            ),
            ClusterHosts::create('127.0.0.1')
        );
        $config = AbtestingConfig::create('foo', 'bar');

        return new AbtestingClient($api, $config);
    }

    /**
     * Test case for AddABTests
     * addABTests with minimal parameters
     */
    public function testAddABTests0()
    {
        $client = $this->getClient();

        $client->addABTests([
            'endAt' => '2022-12-31T00:00:00.000Z',

            'name' => 'myABTest',

            'variant' => [
                ['index' => 'AB_TEST_1', 'trafficPercentage' => 30],

                ['index' => 'AB_TEST_2', 'trafficPercentage' => 50],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/2/abtests',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"endAt\":\"2022-12-31T00:00:00.000Z\",\"name\":\"myABTest\",\"variant\":[{\"index\":\"AB_TEST_1\",\"trafficPercentage\":30},{\"index\":\"AB_TEST_2\",\"trafficPercentage\":50}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Del
     * allow del method for a custom path with minimal parameters
     */
    public function testDel0()
    {
        $client = $this->getClient();

        $client->del('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for Del
     * allow del method for a custom path with all parameters
     */
    public function testDel1()
    {
        $client = $this->getClient();

        $client->del(
            '/test/all',
            ['query' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'DELETE',
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for DeleteABTest
     * deleteABTest
     */
    public function testDeleteABTest0()
    {
        $client = $this->getClient();

        $client->deleteABTest(42);

        $this->assertRequests([
            [
                'path' => '/2/abtests/42',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for Get
     * allow get method for a custom path with minimal parameters
     */
    public function testGet0()
    {
        $client = $this->getClient();

        $client->get('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for Get
     * allow get method for a custom path with all parameters
     */
    public function testGet1()
    {
        $client = $this->getClient();

        $client->get(
            '/test/all',
            ['query' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'GET',
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for GetABTest
     * getABTest
     */
    public function testGetABTest0()
    {
        $client = $this->getClient();

        $client->getABTest(42);

        $this->assertRequests([
            [
                'path' => '/2/abtests/42',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for ListABTests
     * listABTests with minimal parameters
     */
    public function testListABTests0()
    {
        $client = $this->getClient();

        $client->listABTests(
            42,
            21
        );

        $this->assertRequests([
            [
                'path' => '/2/abtests',
                'method' => 'GET',
                'searchParams' => json_decode(
                    "{\"offset\":\"42\",\"limit\":\"21\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Post
     * allow post method for a custom path with minimal parameters
     */
    public function testPost0()
    {
        $client = $this->getClient();

        $client->post('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for Post
     * allow post method for a custom path with all parameters
     */
    public function testPost1()
    {
        $client = $this->getClient();

        $client->post(
            '/test/all',
            ['query' => 'parameters'],
            ['body' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'POST',
                'body' => json_decode("{\"body\":\"parameters\"}"),
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for Put
     * allow put method for a custom path with minimal parameters
     */
    public function testPut0()
    {
        $client = $this->getClient();

        $client->put('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'PUT',
            ],
        ]);
    }

    /**
     * Test case for Put
     * allow put method for a custom path with all parameters
     */
    public function testPut1()
    {
        $client = $this->getClient();

        $client->put(
            '/test/all',
            ['query' => 'parameters'],
            ['body' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'PUT',
                'body' => json_decode("{\"body\":\"parameters\"}"),
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for StopABTest
     * stopABTest
     */
    public function testStopABTest0()
    {
        $client = $this->getClient();

        $client->stopABTest(42);

        $this->assertRequests([
            [
                'path' => '/2/abtests/42/stop',
                'method' => 'POST',
            ],
        ]);
    }
}

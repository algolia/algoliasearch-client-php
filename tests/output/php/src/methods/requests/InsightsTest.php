<?php

namespace Algolia\AlgoliaSearch\Test\Api;

use Algolia\AlgoliaSearch\Api\InsightsClient;
use Algolia\AlgoliaSearch\Configuration\InsightsConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * InsightsTest
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class InsightsTest extends TestCase implements HttpClientInterface
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
            InsightsConfig::create(
                getenv('ALGOLIA_APP_ID'),
                getenv('ALGOLIA_API_KEY')
            ),
            ClusterHosts::create('127.0.0.1')
        );
        $config = InsightsConfig::create('foo', 'bar');

        return new InsightsClient($api, $config);
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
     * Test case for PushEvents
     * pushEvents
     */
    public function testPushEvents0()
    {
        $client = $this->getClient();

        $client->pushEvents([
            'events' => [
                [
                    'eventType' => 'click',

                    'eventName' => 'Product Clicked',

                    'index' => 'products',

                    'userToken' => 'user-123456',

                    'timestamp' => 1641290601962,

                    'objectIDs' => ['9780545139700', '9780439784542'],

                    'queryID' => '43b15df305339e827f0ac0bdc5ebcaa7',

                    'positions' => [7, 6],
                ],

                [
                    'eventType' => 'view',

                    'eventName' => 'Product Detail Page Viewed',

                    'index' => 'products',

                    'userToken' => 'user-123456',

                    'timestamp' => 1641290601962,

                    'objectIDs' => ['9780545139700', '9780439784542'],
                ],

                [
                    'eventType' => 'conversion',

                    'eventName' => 'Product Purchased',

                    'index' => 'products',

                    'userToken' => 'user-123456',

                    'timestamp' => 1641290601962,

                    'objectIDs' => ['9780545139700', '9780439784542'],

                    'queryID' => '43b15df305339e827f0ac0bdc5ebcaa7',
                ],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/events',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"events\":[{\"eventType\":\"click\",\"eventName\":\"Product Clicked\",\"index\":\"products\",\"userToken\":\"user-123456\",\"timestamp\":1641290601962,\"objectIDs\":[\"9780545139700\",\"9780439784542\"],\"queryID\":\"43b15df305339e827f0ac0bdc5ebcaa7\",\"positions\":[7,6]},{\"eventType\":\"view\",\"eventName\":\"Product Detail Page Viewed\",\"index\":\"products\",\"userToken\":\"user-123456\",\"timestamp\":1641290601962,\"objectIDs\":[\"9780545139700\",\"9780439784542\"]},{\"eventType\":\"conversion\",\"eventName\":\"Product Purchased\",\"index\":\"products\",\"userToken\":\"user-123456\",\"timestamp\":1641290601962,\"objectIDs\":[\"9780545139700\",\"9780439784542\"],\"queryID\":\"43b15df305339e827f0ac0bdc5ebcaa7\"}]}"
                ),
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
}

<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Config\RecommendConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RecommendClient;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Psr\Http\Message\RequestInterface;

class RecommendClientTest extends BaseTest implements HttpClientInterface
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

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $this->recordedRequests[] = $request;

        return new Response(200, [], '{}');
    }

    protected function getClient()
    {
        $api = new ApiWrapper($this, RecommendConfig::create(), ClusterHosts::create('127.0.0.1'));
        $config = RecommendConfig::create('foo', 'bar');

        return new RecommendClient($api, $config);
    }

    public function testGetRecommendations()
    {
        $client = $this->getClient();

        // Test method with 'bought-together' model
        $client->getRecommendations([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
                'model' => RecommendClient::BOUGHT_TOGETHER,
            ],
        ]);

        // Test method with 'related-products' model
        $client->getRecommendations([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
                'model' => RecommendClient::RELATED_PRODUCT,
            ],
        ]);

        // Test method with multiple requests and specified thresholds
        $client->getRecommendations([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE-1',
                'model' => RecommendClient::RELATED_PRODUCT,
                'threshold' => 0,
            ],
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE-2',
                'model' => RecommendClient::RELATED_PRODUCT,
                'threshold' => 0,
            ],
        ]);

        // Test overrides undefined threshold with default value
        $client->getRecommendations([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
                'model' => RecommendClient::BOUGHT_TOGETHER,
                'threshold' => null,
            ],
        ]);

        // Test threshold is overriden by specified value
        $client->getRecommendations([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
                'model' => RecommendClient::BOUGHT_TOGETHER,
                'threshold' => 42,
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"bought-together","threshold":0}]}',
            ],
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"related-products","threshold":0}]}',
            ],
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE-1","model":"related-products","threshold":0},{"indexName":"products","objectID":"B018APC4LE-2","model":"related-products","threshold":0}]}',
            ],
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"bought-together","threshold":0}]}',
            ],
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"bought-together","threshold":42}]}',
            ],
        ]);
    }

    public function testGetRelatedProducts()
    {
        $client = $this->getClient();

        $client->getRelatedProducts([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"related-products","threshold":0}]}',
            ],
        ]);
    }

    public function testGetFrequentlyBoughtTogether()
    {
        $client = $this->getClient();

        $client->getFrequentlyBoughtTogether([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
            ],
        ]);

        // Check if `fallbackParameters` param is not passed for 'bought-together' method
        $client->getFrequentlyBoughtTogether([
            [
                'indexName' => 'products',
                'objectID' => 'B018APC4LE',
                'fallbackParameters' => [
                    'facetFilters' => [],
                ],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"bought-together","threshold":0}]}',
            ],
            [
                'path' => '/1/indexes/*/recommendations',
                'method' => 'POST',
                'body' => '{"requests":[{"indexName":"products","objectID":"B018APC4LE","model":"bought-together","threshold":0}]}',
            ],
        ]);
    }
}

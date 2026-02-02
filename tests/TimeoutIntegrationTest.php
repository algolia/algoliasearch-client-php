<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Configuration\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\CurlHttpClient;
use Algolia\AlgoliaSearch\Http\GuzzleHttpClient;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;

function getTestServerHost(): string
{
    return ('true' === getenv('CI') ? 'localhost' : 'host.docker.internal').':6676';
}

/**
 * @internal
 *
 * @coversNothing
 */
class TimeoutIntegrationTest extends TestCase
{
    private const NON_ROUTABLE_IP = '10.255.255.1';
    private const CONNECT_TIMEOUT_SECONDS = 2;

    // curl connect timeout increases across failed requests: 2s -> 4s -> 6s.
    public function testCurlRetryCountStateful(): void
    {
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new CurlHttpClient(),
            [self::NON_ROUTABLE_IP]
        );

        $times = [];

        for ($i = 0; $i < 3; ++$i) {
            $start = microtime(true);

            try {
                $wrapper->send('GET', '/1/test');
            } catch (UnreachableException $e) {
            }

            $times[] = microtime(true) - $start;

            // verify retry count is actually incrementing
            $retryCount = $clusterHosts->getRetryCount(self::NON_ROUTABLE_IP, false);
            $this->assertEquals($i + 1, $retryCount, 'After request '.($i + 1).', retry count should be '.($i + 1));
        }

        $this->assertGreaterThan(1.5, $times[0], 'Request 1 should take at least ~2s');
        $this->assertLessThan(2.5, $times[0], 'Request 1 should take at most ~2s');

        $this->assertGreaterThan(3.5, $times[1], 'Request 2 should take at least ~4s');
        $this->assertLessThan(4.5, $times[1], 'Request 2 should take at most ~4s');

        $this->assertGreaterThan(5.5, $times[2], 'Request 3 should take at least ~6s');
        $this->assertLessThan(7.0, $times[2], 'Request 3 should take at most ~6s');
    }

    // GuzzleHttpClient connect timeout increases across failed requests: 2s -> 4s -> 6s.
    public function testGuzzleRetryCountStateful(): void
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle is not installed. Install guzzlehttp/guzzle to run this test.');
        }

        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new GuzzleHttpClient(),
            [self::NON_ROUTABLE_IP]
        );

        $times = [];

        for ($i = 0; $i < 3; ++$i) {
            $start = microtime(true);

            try {
                $wrapper->send('GET', '/1/test');
            } catch (UnreachableException $e) {
            }

            $times[] = microtime(true) - $start;

            // verify retry count is actually incrementing
            $retryCount = $clusterHosts->getRetryCount(self::NON_ROUTABLE_IP, false);
            $this->assertEquals($i + 1, $retryCount, 'After request '.($i + 1).', retry count should be '.($i + 1));
        }

        $this->assertGreaterThan(1.5, $times[0], 'Request 1 should take at least ~2s');
        $this->assertLessThan(2.5, $times[0], 'Request 1 should take at most ~2s');

        $this->assertGreaterThan(3.5, $times[1], 'Request 2 should take at least ~4s');
        $this->assertLessThan(4.5, $times[1], 'Request 2 should take at most ~4s');

        $this->assertGreaterThan(5.5, $times[2], 'Request 3 should take at least ~6s');
        $this->assertLessThan(7.0, $times[2], 'Request 3 should take at most ~6s');
    }

    // curl retry_count resets to 0 after successful request.
    public function testCurlRetryCountResets(): void
    {
        $badHost = self::NON_ROUTABLE_IP;
        $goodHostFull = 'http://'.getTestServerHost();

        // Create wrapper with bad host, fail twice to increment retry_count
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new CurlHttpClient(),
            [$badHost]
        );

        for ($i = 0; $i < 2; ++$i) {
            try {
                $wrapper->send('GET', '/test');
            } catch (UnreachableException $e) {
            }
        }

        $badHostRetryCount = $clusterHosts->getRetryCount($badHost, false);
        $this->assertEquals(2, $badHostRetryCount, 'Bad host should have retry_count=2');

        // create wrapper with good host, copy retry_count from bad host
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHostsFullHost(
            new CurlHttpClient(),
            [$goodHostFull]
        );
        $clusterHosts->setRetryCount($goodHostFull, $badHostRetryCount, false);

        // verify setRetryCount actually worked
        $this->assertEquals(
            2,
            $clusterHosts->getRetryCount($goodHostFull, false),
            'setRetryCount should have set retry_count to 2'
        );

        // make successful request
        $response = $wrapper->send('GET', '/1/test/instant');
        $this->assertIsArray($response);

        // verify retry_count was reset
        $this->assertEquals(
            0,
            $clusterHosts->getRetryCount($goodHostFull, false),
            'retry_count should reset to 0 after success'
        );

        // now point back to bad host and verify timeout is ~2s (not ~6s)
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new CurlHttpClient(),
            [$badHost]
        );

        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(1.5, $elapsed, 'After reset should take at least ~2s');
        $this->assertLessThan(2.5, $elapsed, 'After reset should take at most ~2s (not ~6s)');
    }

    // GuzzleHttpClient retry_count resets to 0 after successful request.
    public function testGuzzleRetryCountResets(): void
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle is not installed. Install guzzlehttp/guzzle to run this test.');
        }

        $badHost = self::NON_ROUTABLE_IP;
        $goodHostFull = 'http://'.getTestServerHost();

        // create wrapper with bad host, fail twice to increment retry_count
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new GuzzleHttpClient(),
            [$badHost]
        );

        for ($i = 0; $i < 2; ++$i) {
            try {
                $wrapper->send('GET', '/test');
            } catch (UnreachableException $e) {
            }
        }

        $badHostRetryCount = $clusterHosts->getRetryCount($badHost, false);
        $this->assertEquals(2, $badHostRetryCount, 'Bad host should have retry_count=2');

        // create wrapper with good host, copy retry_count from bad host
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHostsFullHost(
            new GuzzleHttpClient(),
            [$goodHostFull]
        );
        $clusterHosts->setRetryCount($goodHostFull, $badHostRetryCount, false);

        // verify setRetryCount actually worked
        $this->assertEquals(
            2,
            $clusterHosts->getRetryCount($goodHostFull, false),
            'setRetryCount should have set retry_count to 2'
        );

        // make successful request
        $response = $wrapper->send('GET', '/1/test/instant');
        $this->assertIsArray($response);

        // verify retry_count was reset
        $this->assertEquals(
            0,
            $clusterHosts->getRetryCount($goodHostFull, false),
            'retry_count should reset to 0 after success'
        );

        // now point back to bad host and verify timeout is ~2s (not ~6s)
        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new GuzzleHttpClient(),
            [$badHost]
        );

        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(1.5, $elapsed, 'After reset should take at least ~2s');
        $this->assertLessThan(2.5, $elapsed, 'After reset should take at most ~2s (not ~6s)');
    }

    // curl test that multiple hosts maintain independent retry counts.
    public function testCurlMultipleHostsIndependentRetryCount(): void
    {
        $badHost1 = self::NON_ROUTABLE_IP;
        $badHost2 = '10.255.255.2';

        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new CurlHttpClient(),
            [$badHost1, $badHost2]
        );

        // first request will try both hosts sequentially
        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        // Verify total time is ~4s
        $this->assertGreaterThan(3.5, $elapsed, 'Should take ~4s total (2s + 2s)');
        $this->assertLessThan(4.5, $elapsed, 'Should take ~4s total, not 2s + 4s (independent retry counts)');

        $this->assertEquals(1, $clusterHosts->getRetryCount($badHost1, false), 'Host 1 should have retry count 1');
        $this->assertEquals(1, $clusterHosts->getRetryCount($badHost2, false), 'Host 2 should have retry count 1');

        // Second request: both hosts at 4s each = ~8s total
        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(7.5, $elapsed, 'Should take ~8s total (4s + 4s)');
        $this->assertLessThan(8.5, $elapsed, 'Should take ~8s total (4s + 4s)');

        $this->assertEquals(2, $clusterHosts->getRetryCount($badHost1, false), 'Host 1 should have retry count 2');
        $this->assertEquals(2, $clusterHosts->getRetryCount($badHost2, false), 'Host 2 should have retry count 2');
    }

    // guzzle test that multiple hosts maintain independent retry counts.
    public function testGuzzleMultipleHostsIndependentRetryCount(): void
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle is not installed. Install guzzlehttp/guzzle to run this test.');
        }

        $badHost1 = self::NON_ROUTABLE_IP;
        $badHost2 = '10.255.255.2';

        [$wrapper, $clusterHosts] = $this->createApiWrapperWithClusterHosts(
            new GuzzleHttpClient(),
            [$badHost1, $badHost2]
        );

        // First request: both hosts at 2s each = ~4s total
        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(3.5, $elapsed, 'Should take ~4s total (2s + 2s)');
        $this->assertLessThan(4.5, $elapsed, 'Should take ~4s total, not 2s + 4s (independent retry counts)');

        $this->assertEquals(1, $clusterHosts->getRetryCount($badHost1, false));
        $this->assertEquals(1, $clusterHosts->getRetryCount($badHost2, false));

        // Second request: both hosts at 4s each = ~8s total
        $start = microtime(true);

        try {
            $wrapper->send('GET', '/test');
        } catch (UnreachableException $e) {
        }
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(7.5, $elapsed, 'Should take ~8s total (4s + 4s)');
        $this->assertLessThan(8.5, $elapsed, 'Should take ~8s total (4s + 4s)');

        $this->assertEquals(2, $clusterHosts->getRetryCount($badHost1, false));
        $this->assertEquals(2, $clusterHosts->getRetryCount($badHost2, false));
    }

    // curl 1 good host and 1 bad host
    public function testCurlMultipleHostsMixedIndependentRetryCount(): void
    {
        $badHostFull = 'https://'.self::NON_ROUTABLE_IP.':443';
        $goodHostFull = 'http://'.getTestServerHost();

        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts([$badHostFull, $goodHostFull])
        ;

        $clusterHosts = ClusterHosts::create([
            $badHostFull => 10,   // Try first
            $goodHostFull => 0,    // Try second
        ]);

        $requestOptionsFactory = new RequestOptionsFactory($config);

        $wrapper = new ApiWrapper(
            new CurlHttpClient(),
            $config,
            $clusterHosts,
            $requestOptionsFactory
        );

        // First request
        $start = microtime(true);
        $response = $wrapper->send('GET', '/1/test/instant');
        $elapsed = microtime(true) - $start;

        $this->assertIsArray($response);
        $this->assertGreaterThan(1.5, $elapsed, 'Should take ~2s (bad host timeout + instant success)');
        $this->assertLessThan(2.5, $elapsed, 'Should take ~2s, not longer');

        $this->assertEquals(1, $clusterHosts->getRetryCount($badHostFull, false), 'Bad host should have retry count 1');
        $this->assertEquals(0, $clusterHosts->getRetryCount($goodHostFull, false), 'Good host should have retry count 0');

        // Second request
        $start = microtime(true);
        $response = $wrapper->send('GET', '/1/test/instant');
        $elapsed = microtime(true) - $start;

        $this->assertIsArray($response);
        $this->assertGreaterThan(3.5, $elapsed, 'Should take ~4s (bad host with increased timeout)');
        $this->assertLessThan(4.5, $elapsed, 'Should take ~4s total');

        $this->assertEquals(2, $clusterHosts->getRetryCount($badHostFull, false), 'Bad host should have retry count 2');
        $this->assertEquals(0, $clusterHosts->getRetryCount($goodHostFull, false), 'Good host should still have retry count 0');
    }

    // guzzle 1 good host and 1 bad host
    public function testGuzzleMultipleHostsMixedIndependentRetryCount(): void
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle is not installed. Install guzzlehttp/guzzle to run this test.');
        }

        $badHostFull = 'https://'.self::NON_ROUTABLE_IP.':443';
        $goodHostFull = 'http://'.getTestServerHost();

        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts([$badHostFull, $goodHostFull])
        ;

        $clusterHosts = ClusterHosts::create([
            $badHostFull => 10,
            $goodHostFull => 0,
        ]);

        $requestOptionsFactory = new RequestOptionsFactory($config);

        $wrapper = new ApiWrapper(
            new GuzzleHttpClient(),
            $config,
            $clusterHosts,
            $requestOptionsFactory
        );

        // First request
        $start = microtime(true);
        $response = $wrapper->send('GET', '/1/test/instant');
        $elapsed = microtime(true) - $start;

        $this->assertIsArray($response);
        $this->assertGreaterThan(1.5, $elapsed, 'Should take ~2s');
        $this->assertLessThan(2.5, $elapsed, 'Should take ~2s');

        $this->assertEquals(1, $clusterHosts->getRetryCount($badHostFull, false));
        $this->assertEquals(0, $clusterHosts->getRetryCount($goodHostFull, false));

        // Second request
        $start = microtime(true);
        $response = $wrapper->send('GET', '/1/test/instant');
        $elapsed = microtime(true) - $start;

        $this->assertIsArray($response);
        $this->assertGreaterThan(3.5, $elapsed, 'Should take ~4s');
        $this->assertLessThan(4.5, $elapsed, 'Should take ~4s');

        $this->assertEquals(2, $clusterHosts->getRetryCount($badHostFull, false));
        $this->assertEquals(0, $clusterHosts->getRetryCount($goodHostFull, false));
    }

    private function createApiWrapperWithClusterHosts($httpClient, array $hosts): array
    {
        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
        ;

        $clusterHosts = ClusterHosts::create($hosts);
        $requestOptionsFactory = new RequestOptionsFactory($config);

        $wrapper = new ApiWrapper(
            $httpClient,
            $config,
            $clusterHosts,
            $requestOptionsFactory
        );

        return [$wrapper, $clusterHosts];
    }

    private function createApiWrapperWithClusterHostsFullHost($httpClient, array $hosts): array
    {
        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts($hosts)
        ;

        $clusterHosts = ClusterHosts::create($hosts);
        $requestOptionsFactory = new RequestOptionsFactory($config);

        $wrapper = new ApiWrapper(
            $httpClient,
            $config,
            $clusterHosts,
            $requestOptionsFactory
        );

        return [$wrapper, $clusterHosts];
    }
}

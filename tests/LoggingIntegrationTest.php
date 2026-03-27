<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Api\IngestionClient;
use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Configuration\IngestionConfig;
use Algolia\AlgoliaSearch\Configuration\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\CurlHttpClient;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\Log\DebugLogger;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\AbstractLogger;

function getLoggingTestServerHost(): string
{
    return ('true' === getenv('CI') ? 'localhost' : 'host.docker.internal').':6676';
}

/**
 * @internal
 *
 * @coversNothing
 */
class LoggingIntegrationTest extends TestCase
{
    private const NON_ROUTABLE_IP = '10.255.255.1';
    private const CONNECT_TIMEOUT_SECONDS = 2;

    private array $logs = [];

    protected function setUp(): void
    {
        $this->logs = [];

        $testCase = $this;
        Algolia::setLogger(new class($testCase) extends AbstractLogger {
            private LoggingIntegrationTest $testCase;

            public function __construct(LoggingIntegrationTest $testCase)
            {
                $this->testCase = $testCase;
            }

            public function log($level, $message, array $context = []): void
            {
                $this->testCase->captureLog($level, $message, $context);
            }
        });
    }

    protected function tearDown(): void
    {
        Algolia::setLogger(new DebugLogger());
    }

    public function captureLog(string $level, string $message, array $context): void
    {
        $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    public function testInfoRequestSummaryFormat(): void
    {
        $this->createApiWrapperWithFullHosts(['http://'.getLoggingTestServerHost()])
            ->send('GET', '/1/test/instant')
        ;

        $this->assertLogMatches('info', '/^Algolia API client: GET .+ - \d{3} \(\d+ms\)$/', 'INFO log should match "{METHOD} {URL} - {STATUS} ({DURATION}ms)"');
    }

    public function testDebugRequestResponseDetails(): void
    {
        $this->createApiWrapperWithFullHosts(['http://'.getLoggingTestServerHost()])
            ->send('GET', '/1/test/instant')
        ;

        $messages = array_column($this->getLogsByLevel('debug'), 'message');
        $this->assertContainsPrefix($messages, 'Algolia API client: Request headers:');
        $this->assertContainsPrefix($messages, 'Algolia API client: Response headers:');
        $this->assertContainsPrefix($messages, 'Algolia API client: Response body:');
    }

    public function testApiKeyFilteredFromHeadersAndUrls(): void
    {
        $this->createApiWrapperWithFullHosts(['http://'.getLoggingTestServerHost()])
            ->send('GET', '/1/test/instant', ['apiKey' => 'secret-in-url'])
        ;

        foreach ($this->logs as $log) {
            $this->assertStringNotContainsString('test-api-key', $log['message'], 'API key must not appear in any log message');
            $this->assertStringNotContainsString('secret-in-url', $log['message'], 'URL apiKey param must not appear in any log message');
        }

        $headerLog = $this->assertLogMatches('debug', '/Request headers:/', 'Should have a debug log with request headers');
        $this->assertStringContainsString('[FILTERED]', $headerLog);
    }

    public function testRetryLogFormats(): void
    {
        $host1 = self::NON_ROUTABLE_IP;
        $host2 = '10.255.255.2';

        try {
            $this->createApiWrapperWithHosts([$host1, $host2])->send('GET', '/1/test');
        } catch (UnreachableException $e) {
        }

        // INFO: failed attempt
        $this->assertLogMatches('info', '/Attempt \d+\/\d+ failed for GET/', 'INFO log should match "Attempt {N}/{MAX} failed for {METHOD} {PATH}"');

        // DEBUG: attempt details with host and reason
        $this->assertLogMatches('debug', '/Attempt \d+\/\d+: .+ on '.$host1.'/', 'DEBUG log should include failed host and reason');
    }

    // Spec: ERROR "Request failed after {MAX} retries: {ERROR_MESSAGE}"
    public function testRetryExhaustionLogsError(): void
    {
        try {
            $this->createApiWrapperWithHosts([self::NON_ROUTABLE_IP])->send('GET', '/1/test');
        } catch (UnreachableException $e) {
        }

        $this->assertLogMatches('error', '/Request failed after \d+ retries/', 'ERROR log should match "Request failed after {MAX} retries: ..."');
    }

    // Spec: INFO "Request completed after {N} retries (total: {DURATION}ms)"
    public function testRetryCompletionLogsInfo(): void
    {
        $badHostFull = 'https://'.self::NON_ROUTABLE_IP.':443';
        $goodHostFull = 'http://'.getLoggingTestServerHost();

        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts([$badHostFull, $goodHostFull])
        ;

        $clusterHosts = ClusterHosts::create([
            $badHostFull => 10,
            $goodHostFull => 0,
        ]);

        $wrapper = new ApiWrapper(
            new CurlHttpClient(),
            $config,
            $clusterHosts,
            new RequestOptionsFactory($config)
        );

        $wrapper->send('GET', '/1/test/instant');

        $this->assertLogMatches('info', '/Request completed on attempt \d+\/\d+ \(total: \d+ms\)/', 'INFO log should match "Request completed on attempt {N}/{MAX} (total: {DURATION}ms)"');
    }

    public function testBadRequestLogsWarning(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(403, ['Content-Type' => 'application/json'], '{"message":"Invalid API key","status":403}');
            }
        };

        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts(['http://localhost:80'])
        ;

        $wrapper = new ApiWrapper(
            $mockHttp,
            $config,
            ClusterHosts::create(['http://localhost:80']),
            new RequestOptionsFactory($config)
        );

        try {
            $wrapper->send('GET', '/1/test');
        } catch (BadRequestException $e) {
        }

        $this->assertLogMatches('warning', '/Bad request:/', 'WARNING log should match "Bad request: {ERROR_MESSAGE}"');
    }

    public function testDefaultLoggerProducesNoOutput(): void
    {
        Algolia::setLogger(new DebugLogger());
        $this->logs = [];

        $this->createApiWrapperWithFullHosts(['http://'.getLoggingTestServerHost()])
            ->send('GET', '/1/test/instant')
        ;

        $this->assertEmpty($this->logs, 'Default disabled DebugLogger should produce no output');
    }

    public function testBatchOperationLogging(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(200, ['Content-Type' => 'application/json'], '{"taskID":1,"objectIDs":["1","2"]}');
            }
        };

        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts(['http://localhost:80'])
        ;

        $client = new SearchClient(
            new ApiWrapper($mockHttp, $config, ClusterHosts::create(['http://localhost:80']), new RequestOptionsFactory($config)),
            $config
        );

        $this->logs = [];
        $client->chunkedBatch('test-index', [['objectID' => '1'], ['objectID' => '2']], 'addObject', false);

        $this->assertLogMatches('info', '/Batch operation started: addObject on test-index/', 'INFO log should match "Batch operation started: {OPERATION} on {INDEX}"');
        $this->assertLogMatches('info', '/Batch progress: 2\/2 objects processed/', 'INFO log should match "Batch progress: {N}/{TOTAL} objects processed"');
        $this->assertLogMatches('info', '/Batch operation completed: 2 objects in \d+ms/', 'INFO log should match "Batch operation completed: {TOTAL} objects in {DURATION}ms"');
    }

    public function testChunkedPushLogging(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(200, ['Content-Type' => 'application/json'], '{"runID":"run-1","eventID":"event-1"}');
            }
        };

        $config = IngestionConfig::create('test-app-id', 'test-api-key', 'eu')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts(['http://localhost:80'])
        ;

        $client = new IngestionClient(
            new ApiWrapper($mockHttp, $config, ClusterHosts::create(['http://localhost:80']), new RequestOptionsFactory($config)),
            $config
        );

        $this->logs = [];
        $client->chunkedPush('test-index', [['objectID' => '1'], ['objectID' => '2']], 'addObject', false);

        $this->assertLogMatches('info', '/Batch operation started: addObject on test-index/', 'INFO log should match "Batch operation started: {OPERATION} on {INDEX}"');
        $this->assertLogMatches('info', '/Batch progress: 2\/2 objects processed/', 'INFO log should match "Batch progress: {N}/{TOTAL} objects processed"');
        $this->assertLogMatches('info', '/Batch operation completed: 2 objects in \d+ms/', 'INFO log should match "Batch operation completed: {TOTAL} objects in {DURATION}ms"');
    }

    public function testClientInitLogging(): void
    {
        SearchClient::create('test-app-id', 'test-api-key');

        $this->assertLogMatches('info', '/Algolia API client: Algolia SearchClient initialized \(appId: test-app-id\)/', 'INFO log should match "Algolia {ClientName} initialized (appId: {appId})"');
        $this->assertLogMatches('debug', '/Algolia API client: WARNING: DEBUG level logging is enabled/', 'DEBUG log should warn about DEBUG level logging');
    }

    public function testDeserializationErrorLogsError(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(200, ['Content-Type' => 'application/json'], 'not valid json');
            }
        };

        $wrapper = $this->createApiWrapperWithMockHttp($mockHttp);

        try {
            $wrapper->send('GET', '/1/test');
        } catch (\Exception $e) {
        }

        $this->assertLogMatches('error', '/Failed to deserialize response:/', 'ERROR log should match "Failed to deserialize response: {ERROR}"');
    }

    public function testSerializationTimingLog(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(200, ['Content-Type' => 'application/json'], '{}');
            }
        };

        $wrapper = $this->createApiWrapperWithMockHttp($mockHttp);
        $wrapper->send('POST', '/1/test', ['body' => ['key' => 'value']]);

        $this->assertLogMatches('debug', '/Request body serialized in \d+ms/', 'DEBUG log should match "Request body serialized in {N}ms"');
    }

    public function testDeserializationTimingLog(): void
    {
        $this->createApiWrapperWithFullHosts(['http://'.getLoggingTestServerHost()])
            ->send('GET', '/1/test/instant')
        ;

        $this->assertLogMatches('debug', '/Response body deserialized in \d+ms/', 'DEBUG log should match "Response body deserialized in {N}ms"');
    }

    public function testSerializationErrorLogsError(): void
    {
        $mockHttp = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
            {
                return new Response(200, ['Content-Type' => 'application/json'], '{}');
            }
        };

        $wrapper = $this->createApiWrapperWithMockHttp($mockHttp);

        // NAN cannot be encoded to JSON
        try {
            $wrapper->send('POST', '/1/test', ['body' => ['value' => NAN]]);
        } catch (\Exception $e) {
        }

        $this->assertLogMatches('error', '/Serialization error:/', 'ERROR log should match "Serialization error: {ERROR}"');
    }

    public function testDebugWarningLoggedOnlyOnce(): void
    {
        SearchClient::create('test-app-id', 'test-api-key');
        SearchClient::create('test-app-id', 'test-api-key');

        $debugWarnings = array_filter($this->logs, fn ($log) => 'debug' === $log['level'] && str_contains($log['message'], 'WARNING: DEBUG level logging is enabled'));
        $this->assertCount(1, $debugWarnings, 'DEBUG warning should only be logged once across multiple client initializations');
    }

    private function createApiWrapperWithMockHttp(HttpClientInterface $mockHttp): ApiWrapper
    {
        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts(['http://localhost:80'])
        ;

        return new ApiWrapper($mockHttp, $config, ClusterHosts::create(['http://localhost:80']), new RequestOptionsFactory($config));
    }

    private function createApiWrapperWithHosts(array $hosts): ApiWrapper
    {
        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
        ;

        return new ApiWrapper(new CurlHttpClient(), $config, ClusterHosts::create($hosts), new RequestOptionsFactory($config));
    }

    private function createApiWrapperWithFullHosts(array $hosts): ApiWrapper
    {
        $config = SearchConfig::create('test-app-id', 'test-api-key')
            ->setConnectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->setFullHosts($hosts)
        ;

        return new ApiWrapper(new CurlHttpClient(), $config, ClusterHosts::create($hosts), new RequestOptionsFactory($config));
    }

    private function getLogsByLevel(string $level): array
    {
        return array_values(array_filter($this->logs, fn ($log) => $log['level'] === $level));
    }

    private function assertLogMatches(string $level, string $regex, string $description = ''): string
    {
        foreach ($this->getLogsByLevel($level) as $log) {
            if (preg_match($regex, $log['message'])) {
                $this->addToAssertionCount(1);

                return $log['message'];
            }
        }

        $actual = implode("\n  ", array_map(fn ($l) => "[{$l['level']}] {$l['message']}", $this->logs)) ?: '(no logs captured)';
        $this->fail("{$description}\nExpected {$level} log matching: {$regex}\nCaptured logs:\n  {$actual}");
    }

    private function assertContainsPrefix(array $messages, string $prefix): void
    {
        foreach ($messages as $msg) {
            if (str_starts_with($msg, $prefix)) {
                $this->addToAssertionCount(1);

                return;
            }
        }
        $this->fail("No message starts with '{$prefix}'");
    }
}

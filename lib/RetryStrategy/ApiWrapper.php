<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\Configuration;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Exceptions\TimeoutException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Request;
use Algolia\AlgoliaSearch\Http\Psr7\Uri;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\Support\Helpers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class ApiWrapper implements ApiWrapperInterface
{
    private const COMPRESSION_THRESHOLD = 750;

    /**
     * @var HttpClientInterface
     */
    private $http;

    /**
     * @var ClusterHosts
     */
    private $clusterHosts;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var RequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $jsonOptions = 0;

    public function __construct(
        HttpClientInterface $http,
        Configuration $config,
        ClusterHosts $clusterHosts,
        ?RequestOptionsFactory $RqstOptsFactory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->http = $http;
        $this->clusterHosts = $clusterHosts;
        $this->config = $config;
        $this->requestOptionsFactory
            = $RqstOptsFactory ?: new RequestOptionsFactory($config);
        $this->logger = $logger ?: Algolia::getLogger();
        if (defined('JSON_UNESCAPED_UNICODE')) {
            // `JSON_UNESCAPED_UNICODE` is introduced in PHP 5.4.0
            $this->jsonOptions = JSON_UNESCAPED_UNICODE;
        }
    }

    public function sendRequest(
        $method,
        $path,
        $data = [],
        $requestOptions = [],
        $useReadTransporter = false,
        $returnHttpInfo = false
    ) {
        /**
         * Some POST methods in the Algolia REST API uses the `read` transporter.
         * This information is defined at the spec level.
         */
        $isRead = $useReadTransporter || 'GET' === mb_strtoupper($method);

        if ($isRead || 'DELETE' === mb_strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess(
                $requestOptions
            );
        } else {
            $requestOptions = $this->requestOptionsFactory->create(
                $requestOptions
            );
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $isRead
                ? $this->clusterHosts->read()
                : $this->clusterHosts->write(),
            $isRead
                ? $requestOptions->getReadTimeout()
                : $requestOptions->getWriteTimeout(),
            $data,
            $returnHttpInfo
        );
    }

    public function send($method, $path, $requestOptions = [], $hosts = null)
    {
        $requestOptions = $this->requestOptionsFactory->create($requestOptions);

        if (null === $hosts) {
            $hosts = $this->clusterHosts->write();
        } elseif (!is_array($hosts)) {
            $hosts = [$hosts];
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $hosts,
            $requestOptions->getWriteTimeout()
        );
    }

    private function request(
        $method,
        $path,
        RequestOptions $requestOptions,
        $hosts,
        $timeout,
        $data = [],
        $returnHttpInfo = false
    ) {
        $uri = $this->createUri($path)
            ->withQuery($requestOptions->getBuiltQueryParameters())
            ->withScheme('https')
        ;

        $body = isset($data)
            ? array_merge($data, $requestOptions->getBody())
            : $data;

        $logParams = [
            'body' => $body,
            'headers' => $this->filterHeaders($requestOptions->getHeaders()),
            'method' => $method,
            'query' => $requestOptions->getQueryParameters(),
        ];

        $hostCount = count($hosts);
        $attemptNumber = 0;
        $totalStartTime = microtime(true);

        foreach ($hosts as $hostUrl) {
            if ($this->config->getHasFullHosts()) {
                $hostParts = explode(':', $hostUrl);
                $uri = $uri->withHost(trim($hostParts[1], '/'))
                    ->withScheme($hostParts[0])
                    ->withPort($hostParts[2])
                ;
            } else {
                $uri = $uri->withHost($hostUrl);
            }

            $sanitizedUrl = $this->sanitizeUrl((string) $uri);
            ++$attemptNumber;

            $request = null;
            $logParams['host'] = (string) $uri;
            $isRead = ($hosts === $this->clusterHosts->read());

            try {
                $request = $this->createRequest(
                    $method,
                    $uri,
                    $requestOptions->getHeaders(),
                    $body
                );

                $this->log(LogLevel::DEBUG, 'Request headers: '.json_encode($logParams['headers']), $logParams);
                if (!empty($logParams['body'])) {
                    $this->log(LogLevel::DEBUG, 'Request body: '.json_encode($logParams['body']), $logParams);
                }
                $retryCount = $this->clusterHosts->getRetryCount($hostUrl, $isRead);
                $connectTimeout = $requestOptions->getConnectTimeout() * ($retryCount + 1);

                $startTime = microtime(true);

                $response = $this->http->sendRequest(
                    $request,
                    $timeout,
                    $connectTimeout
                );

                $statusCode = $response->getStatusCode();
                $durationMs = round((microtime(true) - $startTime) * 1000);

                $responseBody = $this->handleResponse($response, $request, $returnHttpInfo);
                $this->clusterHosts->resetHost($hostUrl);

                $this->log(LogLevel::INFO, $method.' '.$sanitizedUrl.' - '.$statusCode.' ('.$durationMs.'ms)', $logParams);

                if ($attemptNumber > 1) {
                    $totalDurationMs = round((microtime(true) - $totalStartTime) * 1000);
                    $this->log(LogLevel::INFO, 'Request completed on attempt '.$attemptNumber.'/'.$hostCount.' (total: '.$totalDurationMs.'ms)', $logParams);
                }

                // DEBUG: response details
                $this->log(LogLevel::DEBUG, 'Response headers: '.json_encode($response->getHeaders()), $logParams);
                $this->log(LogLevel::DEBUG, 'Response body: '.json_encode($responseBody), $logParams);

                return $responseBody;
            } catch (TimeoutException $e) {
                $this->clusterHosts->timedOut($hostUrl);

                $this->log(LogLevel::INFO, 'Attempt '.$attemptNumber.'/'.$hostCount.' failed for '.$method.' '.$path, $logParams);
                $this->log(LogLevel::DEBUG, 'Attempt '.$attemptNumber.'/'.$hostCount.': Timeout on '.$hostUrl.' after '.($timeout * 1000).'ms ('.$e->getMessage().')', $logParams);
            } catch (RetriableException $e) {
                $this->clusterHosts->failed($hostUrl);

                $this->log(LogLevel::INFO, 'Attempt '.$attemptNumber.'/'.$hostCount.' failed for '.$method.' '.$path, $logParams);
                $this->log(LogLevel::DEBUG, 'Attempt '.$attemptNumber.'/'.$hostCount.': '.$e->getMessage().' on '.$hostUrl, $logParams);
            } catch (BadRequestException $e) {
                $this->log(LogLevel::WARNING, 'Bad request: '.$e->getMessage(), $logParams);

                throw $e;
            } catch (\Exception $e) {
                $this->log(LogLevel::ERROR, 'Generic error: '.$e->getMessage(), $logParams);

                throw $e;
            }
        }

        $this->log(LogLevel::ERROR, 'Request failed after '.$hostCount.' retries: All hosts exhausted', $logParams);

        throw new UnreachableException();
    }

    private function handleResponse(
        ResponseInterface $response,
        RequestInterface $request,
        $returnHttpInfo
    ) {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        if (
            0 === $statusCode
            || ($statusCode >= 100 && $statusCode < 200)
            || $statusCode >= 500
        ) {
            $reason = $response->getReasonPhrase();

            if (
                null === $response->getReasonPhrase()
                || '' === $response->getReasonPhrase()
            ) {
                $reason
                    = $statusCode >= 500
                        ? 'Internal Server Error'
                        : 'Unreachable Host';
            }

            throw new RetriableException('Retriable failure on '.$request->getUri()->getHost().': '.$reason, $statusCode);
        }

        // handle HTML error responses
        if (false !== strpos($response->getHeaderLine('Content-Type'), 'text/html')) {
            throw new AlgoliaException($statusCode.': '.$response->getReasonPhrase(), $statusCode);
        }

        try {
            $deserializeStart = microtime(true);
            $responseArray = Helpers::json_decode($body, true);
            $deserializeDurationMs = round((microtime(true) - $deserializeStart) * 1000);
            $this->log(LogLevel::DEBUG, 'Response body deserialized in '.$deserializeDurationMs.'ms');
        } catch (\InvalidArgumentException $e) {
            $this->log(LogLevel::ERROR, 'Failed to deserialize response: '.$e->getMessage());

            throw $e;
        }

        if (404 === $statusCode) {
            throw new NotFoundException($responseArray['message'], $statusCode);
        }
        if ($statusCode >= 400) {
            throw new BadRequestException($responseArray['message'], $statusCode);
        }
        if (2 !== (int) ($statusCode / 100)) {
            throw new AlgoliaException($statusCode.': '.$body, $statusCode);
        }

        if ($returnHttpInfo) {
            return new AlgoliaResponse(
                $statusCode,
                $response->getHeaders(),
                $body,
                $responseArray
            );
        }

        return $responseArray;
    }

    private function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }
        if (is_string($uri)) {
            return new Uri($uri);
        }

        throw new \InvalidArgumentException('URI must be a string or UriInterface');
    }

    private function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        if (is_array($body)) {
            // Send an empty valid JSON object
            if (empty($body)) {
                $body = '{}';
            } else {
                $serializeStart = microtime(true);
                $body = \json_encode($body, $this->jsonOptions);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $this->log(LogLevel::ERROR, 'Serialization error: '.json_last_error_msg());

                    throw new \InvalidArgumentException('json_encode error: '.json_last_error_msg());
                }
                $serializeDurationMs = round((microtime(true) - $serializeStart) * 1000);
                $this->log(LogLevel::DEBUG, 'Request body serialized in '.$serializeDurationMs.'ms');
            }
        }

        if ('gzip' === $this->config->getCompressionType() && is_string($body) && strlen($body) >= self::COMPRESSION_THRESHOLD) {
            $body = \gzencode($body);
            $headers['content-encoding'] = 'gzip';
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @param string $level
     * @param string $message
     */
    private function log($level, $message, array $context = [])
    {
        $this->logger->log($level, 'Algolia API client: '.$message, $context);
    }

    private function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = ['x-algolia-api-key', 'authorization'];
        $filtered = [];
        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), $sensitiveHeaders, true)) {
                $filtered[$name] = '[FILTERED]';
            } else {
                $filtered[$name] = $value;
            }
        }

        return $filtered;
    }

    private function sanitizeUrl(string $url): string
    {
        return preg_replace('/([?&])(apiKey|x-algolia-api-key)=[^&]+/', '$1$2=[FILTERED]', $url);
    }
}

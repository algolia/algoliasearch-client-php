<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Configuration\Configuration;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
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
        $this->requestOptionsFactory =
            $RqstOptsFactory ?: new RequestOptionsFactory($config);
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
        $useReadTransporter = false
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
            $data
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
        $data = []
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
            'headers' => $requestOptions->getHeaders(),
            'method' => $method,
            'query' => $requestOptions->getQueryParameters(),
        ];

        $retry = 1;
        foreach ($hosts as $host) {
            if ($this->config->getHasFullHosts()) {
                $host = explode(':', $host);
                $uri = $uri->withHost(trim($host[1], '/'))
                    ->withScheme($host[0])
                    ->withPort($host[2])
                ;
            } else {
                $uri = $uri->withHost($host);
            }

            $request = null;
            $logParams['retryNumber'] = $retry;
            $logParams['host'] = (string) $uri;

            try {
                $request = $this->createRequest(
                    $method,
                    $uri,
                    $requestOptions->getHeaders(),
                    $body
                );

                $this->log(LogLevel::DEBUG, 'Sending request.', $logParams);

                $response = $this->http->sendRequest(
                    $request,
                    $timeout * $retry,
                    $requestOptions->getConnectTimeout() * $retry
                );

                $responseBody = $this->handleResponse($response, $request);

                $logParams['response'] = $responseBody;
                $this->log(LogLevel::DEBUG, 'Response received.', $logParams);

                return $responseBody;
            } catch (RetriableException $e) {
                $this->log(
                    LogLevel::DEBUG,
                    'Host failed.',
                    array_merge($logParams, [
                        'description' => $e->getMessage(),
                    ])
                );

                $this->clusterHosts->failed($host);
            } catch (BadRequestException $e) {
                unset($logParams['body'], $logParams['headers']);
                $logParams['description'] = $e->getMessage();
                $this->log(LogLevel::WARNING, 'Bad request.', $logParams);

                throw $e;
            } catch (\Exception $e) {
                unset($logParams['body'], $logParams['headers']);
                $logParams['description'] = $e->getMessage();
                $this->log(LogLevel::ERROR, 'Generic error.', $logParams);

                throw $e;
            }

            ++$retry;
        }

        throw new UnreachableException();
    }

    private function handleResponse(
        ResponseInterface $response,
        RequestInterface $request
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
                $reason =
                    $statusCode >= 500
                        ? 'Internal Server Error'
                        : 'Unreachable Host';
            }

            throw new RetriableException('Retriable failure on '.$request->getUri()->getHost().': '.$reason, $statusCode);
        }

        // handle HTML error responses
        if (false !== strpos($response->getHeaderLine('Content-Type'), 'text/html')) {
            throw new AlgoliaException($statusCode.': '.$response->getReasonPhrase(), $statusCode);
        }

        $responseArray = Helpers::json_decode($body, true);

        if (404 === $statusCode) {
            throw new NotFoundException($responseArray['message'], $statusCode);
        }
        if ($statusCode >= 400) {
            throw new BadRequestException($responseArray['message'], $statusCode);
        }
        if (2 !== (int) ($statusCode / 100)) {
            throw new AlgoliaException($statusCode.': '.$body, $statusCode);
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
                $body = \json_encode($body, $this->jsonOptions);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new \InvalidArgumentException('json_encode error: '.json_last_error_msg());
                }
            }
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
}

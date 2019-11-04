<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Config\AbstractConfig;
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
     * @var AbstractConfig
     */
    private $config;

    /**
     * @var \Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts
     */
    private $clusterHosts;

    /**
     * @var RequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HttpClientInterface $http,
        AbstractConfig $config,
        ClusterHosts $clusterHosts,
        RequestOptionsFactory $RqstOptsFactory = null,
        LoggerInterface $logger = null
    ) {
        $this->http = $http;
        $this->config = $config;
        $this->clusterHosts = $clusterHosts;
        $this->requestOptionsFactory = $RqstOptsFactory ?: new RequestOptionsFactory($config);
        $this->logger = $logger ?: Algolia::getLogger();
    }

    public function read($method, $path, $requestOptions = array(), $defaultRequestOptions = array())
    {
        if ('GET' === strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions, $defaultRequestOptions);
        } else {
            $requestOptions = $this->requestOptionsFactory->create($requestOptions, $defaultRequestOptions);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $this->clusterHosts->read(),
            $requestOptions->getReadTimeout()
        );
    }

    public function write($method, $path, $data = array(), $requestOptions = array(), $defaultRequestOptions = array())
    {
        if ('DELETE' === strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions, $defaultRequestOptions);
            $data = array();
        } else {
            $requestOptions = $this->requestOptionsFactory->create($requestOptions, $defaultRequestOptions);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $this->clusterHosts->write(),
            $requestOptions->getWriteTimeout(),
            $data
        );
    }

    public function send($method, $path, $requestOptions = array(), $hosts = null)
    {
        $requestOptions = $this->requestOptionsFactory->create($requestOptions);

        if (null === $hosts) {
            $hosts = $this->clusterHosts->write();
        } elseif (!is_array($hosts)) {
            $hosts = array($hosts);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $hosts,
            $requestOptions->getWriteTimeout()
        );
    }

    private function request($method, $path, RequestOptions $requestOptions, $hosts, $timeout, $data = array())
    {
        $uri = $this->createUri($path)
            ->withQuery($requestOptions->getBuiltQueryParameters())
            ->withScheme('https');

        $body = array_merge($data, $requestOptions->getBody());

        $logParams = array(
            'body' => $body,
            'headers' => $requestOptions->getHeaders(),
            'method' => $method,
            'query' => $requestOptions->getQueryParameters(),
        );

        $retry = 1;
        foreach ($hosts as $host) {
            $uri = $uri->withHost($host);
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
                $this->log(LogLevel::DEBUG, 'Host failed.', array_merge($logParams, array(
                    'description' => $e->getMessage(),
                )));

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

            $retry++;
        }

        throw new UnreachableException();
    }

    private function handleResponse(ResponseInterface $response, RequestInterface $request)
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        if (0 === $statusCode || $statusCode >= 500) {
            $reason = $response->getReasonPhrase();

            if (null === $response->getReasonPhrase() || '' === $response->getReasonPhrase()) {
                $reason = $statusCode >= 500 ? 'Internal Server Error' : 'Unreachable Host';
            }

            throw new RetriableException('Retriable failure on '.$request->getUri()->getHost().': '.$reason, $statusCode);
        }

        $responseArray = Helpers::json_decode($body, true);

        if (404 == $statusCode) {
            throw new NotFoundException($responseArray['message'], $statusCode);
        } elseif ($statusCode >= 400) {
            throw new BadRequestException($responseArray['message'], $statusCode);
        } elseif (2 != (int) ($statusCode / 100)) {
            throw new AlgoliaException($statusCode.': '.$body, $statusCode);
        }

        return $responseArray;
    }

    private function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        } elseif (is_string($uri)) {
            return new Uri($uri);
        }

        throw new \InvalidArgumentException('URI must be a string or UriInterface');
    }

    private function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    ) {
        if (is_array($body)) {
            // Send an empty body instead of "[]" in case there are
            // no content/params to send
            if (empty($body)) {
                $body = '';
            } else {
                $body = \json_encode($body);
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
    private function log($level, $message, array $context = array())
    {
        $this->logger->log($level, 'Algolia API client: '.$message, $context);
    }
}

<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Interfaces\ConfigInterface;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Psr\Log\LogLevel;

class ApiWrapper
{
    /**
     * @var HttpClientInterface
     */
    private $http;

    /**
     * @var ConfigInterface
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

    public function __construct(
        HttpClientInterface $http,
        ConfigInterface $config,
        ClusterHosts $clusterHosts,
        RequestOptionsFactory $RqstOptsFactory = null
    ) {
        $this->http = $http;
        $this->config = $config;
        $this->clusterHosts = $clusterHosts;
        $this->requestOptionsFactory = $RqstOptsFactory ?: new RequestOptionsFactory($config);
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
        $uri = $this->http
            ->createUri($path)
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
                $request = $this->http->createRequest(
                    $method,
                    $uri,
                    $requestOptions->getHeaders(),
                    $body
                );

                $this->log(LogLevel::DEBUG, 'Sending request.', $logParams);

                $responseBody = $this->http->sendRequest(
                    $request,
                    $timeout * $retry,
                    $requestOptions->getConnectTimeout() * $retry
                );
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

    public function setExtraHeader($headerName, $headerValue)
    {
        $this->requestOptionsFactory->setDefaultHeader($headerName, $headerValue);

        return $this;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    private function log($level, $message, array $context = array())
    {
        Algolia::getLogger()->log($level, 'Algolia API client: '.$message, $context);
    }
}

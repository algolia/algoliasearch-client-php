<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ApiWrapper
{
    /**
     * @var HttpClientInterface
     */
    private $http;

    /**
     * @var ClientConfigInterface
     */
    private $config;

    /**
     * @var RequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * The logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        HttpClientInterface $http,
        ClientConfigInterface $config,
        RequestOptionsFactory $RqstOptsFactory = null,
        LoggerInterface $logger = null
    ) {
        $this->http = $http;
        $this->config = $config;
        $this->requestOptionsFactory = $RqstOptsFactory ?: new RequestOptionsFactory($config);
        $this->logger = $logger ?: ClientConfig::getDefaultLogger();
    }

    public function read($method, $path, $requestOptions = array())
    {
        if ('GET' == strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions);
        } else {
            $requestOptions = $this->requestOptionsFactory->create($requestOptions);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $this->config->getHosts()->read(),
            $requestOptions->getReadTimeout()
        );
    }

    public function write($method, $path, $data = array(), $requestOptions = array())
    {
        if ('DELETE' == strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions);
            $data = array();
        } else {
            $requestOptions = $this->requestOptionsFactory->create($requestOptions);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $this->config->getHosts()->write(),
            $requestOptions->getWriteTimeout(),
            $data
        );
    }

    public function send($method, $path, $requestOptions = array(), $hosts = null)
    {
        $requestOptions = $this->requestOptionsFactory->create($requestOptions);

        if (null === $hosts) {
            $hosts = $this->config->getHosts()->write();
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

        $retry = 1;
        foreach ($hosts as $host) {
            $request = null;
            try {
                $this->logger->debug('Algolia API client: Request attempt.', array(
                    'uri' => $uri->__toString(),
                    'retryNumber' => $retry
                ));

                $request = $this->http->createRequest(
                    $method,
                    $uri->withHost($host),
                    $requestOptions->getHeaders(),
                    $body
                );

                $responseBody = $this->http->sendRequest(
                    $request,
                    $timeout * $retry,
                    $requestOptions->getConnectTimeout() * $retry
                );

                return $responseBody;
            } catch (RetriableException $e) {

                $this->logger->info('Algolia API client: Host failed.', array(
                    'uri' => $uri->__toString(),
                    'host' => $host,
                    'retryNumber' => $retry
                ));

                $this->config->getHosts()->failed($host);
            } catch (BadRequestException $e) {
                throw $e;
            } catch (\Exception $e) {
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
}

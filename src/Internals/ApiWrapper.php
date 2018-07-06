<?php

namespace Algolia\AlgoliaSearch\Internals;

use Algolia\AlgoliaSearch\Config;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Exceptions\UnreachableException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;

class ApiWrapper
{
    /**
     * @var ClusterHosts
     */
    private $clusterHosts;

    /**
     * @var RequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * @var HttpClientInterface
     */
    private $http;

    public function __construct(
        ClusterHosts $clusterHosts,
        RequestOptionsFactory $requestOptionsFactory,
        HttpClientInterface $http
    ) {
        $this->clusterHosts = $clusterHosts;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->http = $http;
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
            $this->clusterHosts->read(),
            $requestOptions->getReadTimeout()
        );
    }

    public function write($method, $path, $requestOptions = array(), $data = array())
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
            $this->clusterHosts->write(),
            $requestOptions->getWriteTimeout(),
            $data
        );
    }

    public function send($method, $hosts, $path, $requestOptions = array()/*s, $timeout*/)
    {
        $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions);

        if (!is_array($hosts)) {
            $hosts = array($hosts);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $hosts,
            $requestOptions->getReadTimeout()
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
            try {
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
                $this->clusterHosts->failed($host);
            } catch (BadRequestException $e) {
                // TODO: something
                dump($request);
                dump('Bad request: '.$e->getMessage());
                throw $e;
            } catch (\Exception $e) {
                // TODO: panic
                dump($e);
                die;
            }

            ++$retry;
        }

        throw new UnreachableException();
    }
}

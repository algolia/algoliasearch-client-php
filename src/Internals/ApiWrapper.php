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

    public function read($method, $path, array $requestOptions = array())
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

    public function write($method, $path, array $requestOptions = array())
    {
        if (isset($requestOptions['timeout'])) {
            $requestOptions['writeTimeout'] = $requestOptions['timeout'];
        }

        if ('DELETE' == strtoupper($method)) {
            $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions);
        } else {
            $requestOptions = $this->requestOptionsFactory->create($requestOptions);
        }

        return $this->request(
            $method,
            $path,
            $requestOptions,
            $this->clusterHosts->write(),
            $requestOptions->getWriteTimeout()
        );
    }

    private function request($method, $path, RequestOptions $requestOptions, $hosts, $timeout)
    {
        $uri = $this->http
            ->createUri($path)
            ->withQuery($requestOptions->getBuiltQuery())
            ->withScheme('https');

        $retry = 1;
        foreach ($hosts as $host) {
            try {
                $request = $this->http->createRequest(
                    $method,
                    $uri->withHost($host),
                    $requestOptions->getHeaders(),
                    $requestOptions->getBody()
                );

                $responseBody = $this->http->sendRequest(
                    $request,
                    $timeout * $retry,
                    $requestOptions->getConnectTimeout() * $retry,
                    Config::getUserAgent()
                );

                return $responseBody;
            } catch (RetriableException $e) {
                $this->clusterHosts->failed($host);
            } catch (BadRequestException $e) {
                // TODO: something
                dump('Bad request: '.$e->getMessage());
                die;
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

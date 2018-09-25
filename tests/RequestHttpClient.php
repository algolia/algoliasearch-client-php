<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class RequestHttpClient implements HttpClientInterface
{
    /**
     * @var \Algolia\AlgoliaSearch\Http\HttpClientInterface
     */
    private $actualClient;

    public function __construct(HttpClientInterface $actualClient)
    {
        $this->actualClient = $actualClient;
    }

    public function createUri($uri)
    {
        return $this->actualClient->createUri($uri);
    }

    public function createRequest($method,
                                  $uri,
                                  array $headers = array(),
                                  $body = null,
                                  $protocolVersion = '1.1')
    {
        return $this->actualClient->createRequest($method, $uri, $headers, $body, $protocolVersion);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        return compact('request', 'timeout', 'connectTimeout');
    }
}

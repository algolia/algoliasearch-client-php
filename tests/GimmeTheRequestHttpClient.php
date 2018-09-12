<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class GimmeTheRequestHttpClient implements HttpClientInterface
{
    private $actual;

    public function __construct(HttpClientInterface $actual)
    {
        $this->actual = $actual;
    }

    public function createUri($uri)
    {
        return $this->actual->createUri($uri);
    }

    public function createRequest($method,
                                  $uri,
                                  array $headers = array(),
                                  $body = null,
                                  $protocolVersion = '1.1')
    {
        return $this->actual->createRequest($method, $uri, $headers, $body, $protocolVersion);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        return array(
            'request' => $request,
            'timeout' => $timeout,
            'connectTimeout' => $connectTimeout,
        );
    }
}

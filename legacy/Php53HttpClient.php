<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Php53HttpClient implements HttpClientInterface
{
    public function createUri($uri): UriInterface
    {
        // TODO: Implement createUri() method.
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    ): RequestInterface {
        // TODO: Implement createRequest() method.
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        // TODO: Implement sendRequest() method.
    }
}

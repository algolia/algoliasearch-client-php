<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface HttpClientInterface
{
    public function createUri($uri): UriInterface;

    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): RequestInterface;

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout);
}

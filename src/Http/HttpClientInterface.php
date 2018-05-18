<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    public function createUri($uri);

    public function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    );

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout, $userAgent);
}

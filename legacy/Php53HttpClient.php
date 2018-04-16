<?php

namespace Algolia\AlgoliaSearch\Legacy;

use Algolia\AlgoliaSearch\Legacy\Psr7\Request;
use Algolia\AlgoliaSearch\Legacy\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Php53HttpClient implements HttpClientInterface
{
    public function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        } elseif (is_string($uri)) {
            return new Uri($uri);
        }
        throw new \InvalidArgumentException('URI must be a string or UriInterface');
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    ) {
        // TODO: Implement createRequest() method.
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        // TODO: Implement sendRequest() method.
    }
}

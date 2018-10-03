<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class NullHttpClient implements HttpClientInterface
{
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        return new Response(201, array(), '[]');
    }
}

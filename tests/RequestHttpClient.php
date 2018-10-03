<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class RequestHttpClient implements HttpClientInterface
{
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $e = new RequestException();

        throw $e->setRequest($request);
    }
}

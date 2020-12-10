<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class RequestHttpClient implements HttpClientInterface
{
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $e = new RequestException('Use a try/catch to get the request');

        throw $e->setRequest($request);
    }
}

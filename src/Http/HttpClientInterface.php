<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout);
}

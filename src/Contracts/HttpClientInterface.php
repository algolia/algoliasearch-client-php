<?php

namespace Algolia\AlgoliaSearch\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * Handle
     * @param RequestInterface $request
     * @param $timeout
     * @param $connectTimeout
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, $timeout, $connectTimeout): ResponseInterface;
}

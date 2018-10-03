<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    /**
     * The method takes a PSR request and 2 timeouts, dispatch
     * the call and must return a PSR Response.
     *
     * If the HTTP layer throws exception in case of error 4xx or 5xx
     * for instance, they must be converted to a Response to keep
     * the retry strategy working as expected.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param                                    $timeout
     * @param                                    $connectTimeout
     *
     * @return mixed
     */
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout);
}

<?php

namespace Algolia\AlgoliaSearch\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

class Psr18HttpClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        return $this->httpClient->sendRequest($request);
    }
}

<?php

namespace Algolia\AlgoliaSearch\Tests\Endpoint;

use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Tests\RequestHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class RequestTestCase extends TestCase
{
    private static $actualHttp;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (null === self::$actualHttp) {
            self::$actualHttp = HttpClientFactory::get(new ClientConfig());
        }

        $actualHttp = self::$actualHttp;
        HttpClientFactory::set(function () use ($actualHttp) {
            return new RequestHttpClient($actualHttp);
        });
    }

    protected function assertEndpointEquals(RequestInterface $request, $endpoint)
    {
        $this->assertEquals($endpoint, $request->getUri()->getPath());
    }

    protected function assertBodySubset($subset, RequestInterface $request)
    {
        $body = json_decode((string) $request->getBody(), true);
        $this->assertArraySubset($subset, $body);
    }
}

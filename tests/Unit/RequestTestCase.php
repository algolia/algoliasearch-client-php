<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Tests\RequestHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class RequestTestCase extends TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Algolia::setHttpClient(new RequestHttpClient());
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Algolia::resetHttpClient();
    }

    protected function assertEndpointEquals(RequestInterface $request, $endpoint)
    {
        $this->assertEquals($endpoint, $request->getUri()->getPath());
    }

    protected function assertBodySubset($subset, RequestInterface $request)
    {
        $body = json_decode((string) $request->getBody(), true);
        $this->assertArraySubset($subset, $body, true);
    }

    protected function assertEncodedBodySubset(
        $subset,
        RequestInterface $request
    ) {
        $body = json_decode(gzdecode($request->getBody()), true);
        $this->assertArraySubset($subset, $body, true);
    }

    protected function assertQueryParametersSubset(array $subset, RequestInterface $request)
    {
        $params = $this->requestQueryParametersToArray($request);
        $this->assertArraySubset($subset, $params);
    }

    protected function assertQueryParametersNotHasKey($key, RequestInterface $request)
    {
        $params = $this->requestQueryParametersToArray($request);
        $this->assertArrayNotHasKey($key, $params);
    }

    protected function assertBodyEncoded(RequestInterface $request)
    {
        return gzdecode($request->getBody());
    }

    protected function assertHeaderIsSet($headerName, RequestInterface $request)
    {
        $this->assertArrayHasKey($headerName, $request->getHeaders());
    }

    protected function assertHeaderIsNotSet(
        $headerName,
        RequestInterface $request
    ) {
        $this->assertArrayNotHasKey($headerName, $request->getHeaders());
    }

    private function requestQueryParametersToArray(RequestInterface $request)
    {
        $array = array();
        parse_str($request->getUri()->getQuery(), $array);

        return $array;
    }
}

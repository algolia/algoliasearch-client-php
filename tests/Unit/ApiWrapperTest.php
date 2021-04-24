<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ApiWrapperTest extends TestCase implements HttpClientInterface
{
    const UNICODE_STRING = 'Я дивився на шторм – неймовірно красивий і жахаючий.';
    const EXPECTED_JSON = '{"data":"Я дивився на шторм – неймовірно красивий і жахаючий."}';
    const EXPECTED_JSON_PRE_PHP_5_4 = '{"data":"\u042f \u0434\u0438\u0432\u0438\u0432\u0441\u044f \u043d\u0430 \u0448\u0442\u043e\u0440\u043c \u2013 \u043d\u0435\u0439\u043c\u043e\u0432\u0456\u0440\u043d\u043e \u043a\u0440\u0430\u0441\u0438\u0432\u0438\u0439 \u0456 \u0436\u0430\u0445\u0430\u044e\u0447\u0438\u0439."}';

    /**
     * @var RequestInterface[]
     */
    private $recordedRequests = [];

    /**
     * @see https://github.com/algolia/algoliasearch-client-php/issues/546
     */
    public function testUnescapedJsonEncoding()
    {
        $api = new ApiWrapper($this, SearchConfig::create(), ClusterHosts::create('127.0.0.1'));

        $api->write('post', '/', ['data' => self::UNICODE_STRING]);

        $this->assertCount(1, $this->recordedRequests);

        foreach ($this->recordedRequests as $request) {
            $contents = $request->getBody()->getContents();
            if (version_compare(PHP_VERSION, '5.4.0') < 0) {
                $this->assertEquals(ApiWrapperTest::EXPECTED_JSON_PRE_PHP_5_4, $contents);
                continue;
            }
            $this->assertEquals(ApiWrapperTest::EXPECTED_JSON, $contents);
        }
    }

    /**
     * This test-case implements HttpClientInterface
     * in order to assert request body.
     */
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $this->recordedRequests[] = $request;

        return new Response(200, [], '{}');
    }
}

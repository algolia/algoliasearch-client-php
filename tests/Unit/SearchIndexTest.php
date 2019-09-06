<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\SearchClient;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Assert as Assert;

class SearchIndexTest extends TestCase
{
    /** @var SearchConfig */
    protected $config;

    /** @var RequestOptionsFactory */
    protected $requestOptionsFactory;

    public function setUp()
    {
        $this->config = SearchConfig::create('foo', 'bar');
        $this->requestOptionsFactory = new RequestOptionsFactory($this->config);
    }

    public function testFindObject()
    {
        // Test without requestOptions
        $apiWrapperMock = $this->getMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => ''));

                return $requestOptions;
            }))
            ->willReturn(array(
                'hits' => array(array('foo' => 'bar')),
                'nbPages' => 1,
            ));

        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->findObject(
            function () { return true; }
        );

        // Test with requestOptions as an array
        $apiWrapperMock = $this->getMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => 'foo', 'hitsPerPage' => 5));

                return $requestOptions;
            }))
            ->willReturn(array(
                'hits' => array(array('foo' => 'bar')),
                'nbPages' => 1,
            ));

        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->findObject(
            function () { return true; },
            array('query' => 'foo', 'hitsPerPage' => 5)
        );

        // Test with requestOptions as a RequestOptions object
        $apiWrapperMock = $this->getMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => ''));
                Assert::assertArraySubset(array('User-Agent' => 'blabla'), $requestOptions->getHeaders());

                return $requestOptions;
            }))
            ->willReturn(array(
                'hits' => array(array('foo' => 'bar')),
                'nbPages' => 1,
            ));

        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->findObject(
            function () { return true; },
            $this->requestOptionsFactory->create(array('User-Agent' => 'blabla'))
        );
    }

    public function testExistsWithRequestOptions()
    {
        $requestOptions = $this->requestOptionsFactory->create(array('X-Algolia-User-ID' => 'foo'));
        $apiWrapperMock = $this->getMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');

        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertArraySubset(array('X-Algolia-User-ID' => 'foo'), $requestOptions->getHeaders());

                return $requestOptions;
            }))
            ->willReturn(array(
                'hitsPerPage' => 31,
                'userData' => 'API SearchClient copy test',
            ));
        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->exists($requestOptions);
    }

    public function testExistsWithoutRequestOptions()
    {
        $apiWrapperMock = $this->getMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');

        // getVersion is added by default in requestOptions
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInternalType('array', $requestOptions);
                Assert::assertArraySubset(array('getVersion' => 2), $requestOptions);

                return $requestOptions;
            }))
            ->willReturn(array(
                'hitsPerPage' => 31,
                'userData' => 'API SearchClient copy test',
            ));
        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->exists();
    }
}

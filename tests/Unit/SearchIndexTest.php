<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\AssertArraySubsetTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SearchIndexTest extends TestCase
{
    use AssertArraySubsetTrait;

    /** @var SearchConfig */
    protected $config;

    /** @var RequestOptionsFactory */
    protected $requestOptionsFactory;

    public function setUp(): void
    {
        $this->config = SearchConfig::create('foo', 'bar');
        $this->requestOptionsFactory = new RequestOptionsFactory($this->config);
    }

    public function testFindObject()
    {
        // Test without requestOptions
        $apiWrapperMock = $this->createMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => ''));

                return true;
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
        $apiWrapperMock = $this->createMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => 'foo', 'hitsPerPage' => 5));

                return true;
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
        $apiWrapperMock = $this->createMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);
                Assert::assertEquals($requestOptions->getBody(), array('page' => 0, 'query' => ''));
                $this->assertArraySubset(array('User-Agent' => 'blabla'), $requestOptions->getHeaders());

                return true;
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
        $apiWrapperMock = $this->createMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');

        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertInstanceOf('Algolia\AlgoliaSearch\RequestOptions\RequestOptions', $requestOptions);

                $headers = $requestOptions->getHeaders();

                Assert::assertArrayHasKey('X-Algolia-User-ID', $headers);
                Assert::assertEquals('foo', $headers['X-Algolia-User-ID']);

                return true;
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
        $apiWrapperMock = $this->createMock('Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface');

        // getVersion is added by default in requestOptions
        $apiWrapperMock->method('read')
            ->with($this->anything(), $this->anything(), $this->callback(function ($requestOptions) {
                Assert::assertIsArray($requestOptions);
                Assert::assertArrayHasKey('getVersion', $requestOptions);
                Assert::assertEquals($requestOptions['getVersion'], 2);

                return true;
            }))
            ->willReturn(array(
                'hitsPerPage' => 31,
                'userData' => 'API SearchClient copy test',
            ));
        $client = new SearchClient($apiWrapperMock, $this->config);
        $client->initIndex('foo')->exists();
    }
}

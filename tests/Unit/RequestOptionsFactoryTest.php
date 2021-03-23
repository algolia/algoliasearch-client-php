<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\Support\UserAgent;
use PHPUnit\Framework\TestCase;

class RequestOptionsFactoryTest extends TestCase
{
    /** @var RequestOptionsFactory */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new RequestOptionsFactory(
            new SearchConfig([
                'appId' => 'Algolia-Id',
                'apiKey' => 'Algolia-Key',
            ])
        );
    }

    /**
     * @dataProvider provideRequestOptionsData
     */
    public function testRequestOptionsFactory($options, $expectedRequestOptions)
    {
        $actual = $this->factory->create($options);

        $expectedRequestOptions['headers'] += ['User-Agent' => UserAgent::get()];

        $this->assertEquals($expectedRequestOptions, [
            'headers' => $actual->getHeaders(),
            'body' => $actual->getBody(),
            'query' => $actual->getQueryParameters(),
            'builtQuery' => $actual->getBuiltQueryParameters(),
            'readTimeout' => $actual->getReadTimeout(),
            'writeTimeout' => $actual->getWriteTimeout(),
            'connectTimeout' => $actual->getConnectTimeout(),
        ]);
    }

    public function provideRequestOptionsData()
    {
        return json_decode(
            file_get_contents(__DIR__.'/../requestOptions.json'),
            true
        );
    }
}

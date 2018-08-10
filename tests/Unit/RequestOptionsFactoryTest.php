<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\Config;
use PHPUnit\Framework\TestCase;

class RequestOptionsFactoryTest extends TestCase
{
    /** @var RequestOptionsFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new RequestOptionsFactory(new ClientConfig(array(
            'appId' => 'Algolia-Id',
            'apiKey' => 'Algolia-Key',
        )));
    }

    /**
     * @dataProvider provideRequestOptionsData
     */
    public function testRequestRequestOptions($options, $expectedRequestOptions)
    {
        $actual = $this->factory->create($options);

        $expectedRequestOptions['headers'] += array("User-Agent" => Config::getUserAgent());

        $this->assertEquals($expectedRequestOptions, array(
            'headers' => $actual->getHeaders(),
            'body' => $actual->getBody(),
            'query' => $actual->getQueryParameters(),
            'builtQuery' => $actual->getBuiltQueryParameters(),
            'readTimeout' => $actual->getReadTimeout(),
            'writeTimeout' => $actual->getWriteTimeout(),
            'connectTimeout' => $actual->getConnectTimeout(),
        ));
    }

    public function provideRequestOptionsData()
    {
        return json_decode(
            file_get_contents(__DIR__.'/../requestOptions.json'),
            true
        );
    }
}

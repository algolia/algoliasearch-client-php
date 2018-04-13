<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Internals\RequestOptionsFactory;
use Algolia\AlgoliaSearch\Tests\TestCase;

class RequestOptionsFactoryTest extends TestCase
{
    /** @var RequestOptionsFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new RequestOptionsFactory('Algolia-Id', 'Algolia-Key');
    }

    /**
     * @dataProvider provideRequestOptionsData
     */
    public function testRequestRequestOptions($options, $expectedRequestOptions)
    {
        $actual = $this->factory->create($options);

        $this->assertEquals($expectedRequestOptions, [
            'headers' => $actual->getHeaders(),
            'body' => $actual->getBody(),
            'query' => $actual->getQuery(),
            'builtQuery' => $actual->getBuiltQuery(),
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

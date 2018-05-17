<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider dataTestApiPathHelper
     */
    public function testApiPathHelper($generatedPath, $expected)
    {
        $this->assertEquals($expected, $generatedPath);
    }

    public function dataTestApiPathHelper()
    {
        return array(array(
            \Algolia\AlgoliaSearch\api_path('/1/indexes/%s/cool/%s', 'index name', 'b&w'),
            '/1/indexes/index+name/cool/b%26w'
        ), array(
            \Algolia\AlgoliaSearch\api_path('/1/indexes/%s/cool/%s', 'index name', urlencode('b&w')),
            '/1/indexes/index+name/cool/b%26w'
        ));
    }
}

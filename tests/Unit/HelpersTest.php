<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Support\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
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
            Helpers::apiPath('/1/indexes/%s/cool/%s', 'index name', 'b&w'),
            '/1/indexes/index+name/cool/b%26w',
        ), array(
            Helpers::apiPath('/1/indexes/%s/cool/%s', 'index name', urlencode('b&w')),
            '/1/indexes/index+name/cool/b%26w',
        ));
    }

    /**
     * @dataProvider dataTestBuildQueryHelper
     */
    public function testBuildQueryHelper($queryParams, $expected)
    {
        $this->assertEquals(
            $expected,
            Helpers::buildQuery($queryParams)
        );
    }

    public function dataTestBuildQueryHelper()
    {
        return array(
            array(
                'queryParams' => array(),
                'expected' => '',
            ),
            array(
                'queryParams' => array('what', 'if', 'I', 'have', 'no', 'key'),
                'expected' => '0=what&1=if&2=I&3=have&4=no&5=key',
            ),
            array(
                'queryParams' => array(
                    'userToken' => 'Thanks',
                    'forward' => true,
                    'copy' => false,
                    'nestedArray' => array(
                        'aaa' => 'bbb',
                        'aaa2' => 'bbb2',
                    ),
                ),
                'expected' => 'userToken=Thanks&forward=true&copy=false&nestedArray=%7B%22aaa%22%3A%22bbb%22%2C%22aaa2%22%3A%22bbb2%22%7D',
            ),
        );
    }

    public function testMapObjectIDs()
    {
        $objects = array(array('primary' => 1, 'name' => 'test'), array('primary' => 2, 'name' => 'cool'));
        $mapped = Helpers::mapObjectIDs('primary', $objects);
        foreach ($mapped as $object) {
            $this->assertEquals($object['primary'], $object['objectID']);
        }
    }

    public function testMapObjectIDsWithMissingPrimary()
    {
        $this->expectException(\Algolia\AlgoliaSearch\Exceptions\MissingObjectId::class);

        $objects = array(array('name' => 'test'), array('primary' => 1, 'name' => 'cool'));
        Helpers::mapObjectIDs('primary', $objects);
    }
}

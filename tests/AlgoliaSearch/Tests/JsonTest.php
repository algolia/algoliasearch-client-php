<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Json;

class JsonTest extends AlgoliaSearchTestCase
{
    public function testJson()
    {
        $array = array('foo' => 'bar');

        $encoded = Json::encode($array);
        $this->assertEquals('{"foo":"bar"}', $encoded);

        $decodedObject = Json::decode($encoded);
        $this->assertEquals($decodedObject->foo, 'bar');

        $decodedArray = Json::decode($encoded, true);
        $this->assertEquals($array, $decodedArray);
    }

    /**
     * @expectedException AlgoliaSearch\AlgoliaException
     */
    public function testMalformedJson()
    {
        $malformedJson = '{"foo":"bar"';
        Json::decode($malformedJson);
    }

    /**
     * @expectedException AlgoliaSearch\AlgoliaException
     */
    public function testMalformedString()
    {
        $utf8String = 'ř';
        $malformedString = substr($utf8String, 0, 1);

        Json::encode($malformedString);
    }

    public function testMultiByteString()
    {
        $multiByteString = '검색 엔진';

        $json = Json::encode($multiByteString);

        $this->assertEquals('"검색 엔진"', $json);
    }
}

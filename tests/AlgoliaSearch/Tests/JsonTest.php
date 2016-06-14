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

    public function testMalformedJson()
    {
        $this->setExpectedException('AlgoliaSearch\AlgoliaException');

        $malformedJson = '{"foo":"bar"';
        Json::decode($malformedJson);
    }

    public function testMalformedString()
    {
        $this->setExpectedException('AlgoliaSearch\AlgoliaException');

        $utf8String = 'ř';
        $malformedString = substr($utf8String, 0, 1);

        Json::encode($malformedString);
    }
}

<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Index;

class IndexTest extends AlgoliaSearchTestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No method named unknown was found.
     */
    public function testShouldThrowAnExceptionIfUnknownMethodIsCalled()
    {
        $clientContextMock = $this->getMockBuilder('AlgoliaSearch\ClientContext')->disableOriginalConstructor()->getMock();
        $client = $this->getMockBuilder('AlgoliaSearch\Client')->disableOriginalConstructor()->getMock();
        $index = new Index($clientContextMock, $client, 'whatever');
        $index->unknown();
    }
}

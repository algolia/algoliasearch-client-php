<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\ClientContext;

class ClientContextTest extends \PHPUnit_Framework_TestCase
{
    public function testRandomReadFallbackHosts()
    {
        $context = new ClientContext('whatever', 'whatever', null);
        $hosts = $context->readHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            $otherContext = new ClientContext('whatever', 'whatever', null);
            if ($hosts !== $otherContext->readHostsArray) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct API endpoint.
        $this->assertEquals('whatever-dsn.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        sort($hosts, SORT_STRING);
        $expectedHosts = array(
            'whatever-1.algolianet.com',
            'whatever-2.algolianet.com',
            'whatever-3.algolianet.com',
            'whatever-dsn.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hosts);
    }

    public function testRandomReadPlacesFallbackHosts()
    {
        $context = new ClientContext('whatever', 'whatever', null, true);
        $hosts = $context->readHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            $otherContext = new ClientContext('whatever', 'whatever', null);
            if ($hosts !== $otherContext->readHostsArray) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct places API endpoint.
        $this->assertEquals('places-dsn.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        sort($hosts, SORT_STRING);
        $expectedHosts = array(
            'places-1.algolianet.com',
            'places-2.algolianet.com',
            'places-3.algolianet.com',
            'places-dsn.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hosts);
    }

    public function testRandomWriteFallbackHosts()
    {
        $context = new ClientContext('whatever', 'whatever', null);
        $hosts = $context->writeHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            $otherContext = new ClientContext('whatever', 'whatever', null);
            if ($hosts !== $otherContext->writeHostsArray) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct API endpoint.
        $this->assertEquals('whatever.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        sort($hosts, SORT_STRING);
        $expectedHosts = array(
            'whatever-1.algolianet.com',
            'whatever-2.algolianet.com',
            'whatever-3.algolianet.com',
            'whatever.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hosts);
    }

    public function testHostsCanBeRotated()
    {
        $context = new ClientContext('whatever', 'whatever', null);
        $initialReadHosts = array('host1.com', 'shared-host.com', 'host3.com');
        $initialWriteHosts = array('write-host1.com', 'shared-host.com', 'write-host3.com');
        $context->readHostsArray = $initialReadHosts;
        $context->writeHostsArray = $initialWriteHosts;

        // Rotate read host.
        $context->rotateHosts('host1.com');
        $this->assertEquals(array('shared-host.com', 'host3.com', 'host1.com'), $context->readHostsArray);
        $this->assertEquals($initialWriteHosts, $context->writeHostsArray);

        // This should change nothing given the current host is shared-host.com.
        $context->rotateHosts('host3.com');
        $this->assertEquals(array('shared-host.com', 'host3.com', 'host1.com'), $context->readHostsArray);
        $this->assertEquals($initialWriteHosts, $context->writeHostsArray);

        // Rotate read write.
        $context->rotateHosts('write-host1.com');
        $this->assertEquals(array('shared-host.com', 'host3.com', 'host1.com'), $context->readHostsArray);
        $this->assertEquals(array('shared-host.com', 'write-host3.com', 'write-host1.com'), $context->writeHostsArray);

        // This should change nothing given the current host is shared-host.com.
        $context->rotateHosts('write-host3.com');
        $this->assertEquals(array('shared-host.com', 'host3.com', 'host1.com'), $context->readHostsArray);
        $this->assertEquals(array('shared-host.com', 'write-host3.com', 'write-host1.com'), $context->writeHostsArray);

        // Should rotate both read and write hosts.
        $context->rotateHosts('shared-host.com');
        $this->assertEquals(array('host3.com', 'host1.com', 'shared-host.com'), $context->readHostsArray);
        $this->assertEquals(array('write-host3.com', 'write-host1.com', 'shared-host.com'), $context->writeHostsArray);
    }
}

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
}

<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\ClientContext;
use AlgoliaSearch\ReadHostsHandler;
use AlgoliaSearch\WriteHostsHandler;

class ClientContextTest extends \PHPUnit_Framework_TestCase
{
    public function testRandomReadFallbackHosts()
    {
        ReadHostsHandler::resetPosition();
        $context = new ClientContext('whatever', 'whatever', null);
        $hosts = $context->readHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            ReadHostsHandler::resetPosition();
            $otherContext = new ClientContext('whatever', 'whatever', null);

            if ($hosts->toArray() !== $otherContext->readHostsArray->toArray()) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct API endpoint.
        $this->assertEquals('whatever-dsn.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        $hostsArray = $hosts->toArray();
        sort($hostsArray, SORT_STRING);
        $expectedHosts = array(
            'whatever-1.algolianet.com',
            'whatever-2.algolianet.com',
            'whatever-3.algolianet.com',
            'whatever-dsn.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hostsArray);
    }

    public function testRandomReadPlacesFallbackHosts()
    {
        ReadHostsHandler::resetPosition();
        $context = new ClientContext('whatever', 'whatever', null, true);
        $hosts = $context->readHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            ReadHostsHandler::resetPosition();
            $otherContext = new ClientContext('whatever', 'whatever', null);
            if ($hosts->toArray() !== $otherContext->readHostsArray->toArray()) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct places API endpoint.
        $this->assertEquals('places-dsn.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        $hostsArray = $hosts->toArray();
        sort($hostsArray, SORT_STRING);

        $expectedHosts = array(
            'places-1.algolianet.com',
            'places-2.algolianet.com',
            'places-3.algolianet.com',
            'places-dsn.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hostsArray);
    }

    public function testRandomWriteFallbackHosts()
    {
        WriteHostsHandler::resetPosition();
        $context = new ClientContext('whatever', 'whatever', null);
        $hosts = $context->writeHostsArray;

        // Here we check that different calls results in the hosts being in and different order.
        $isRandom = false;
        for ($i = 0; $i < 100; $i++) {
            WriteHostsHandler::resetPosition();
            $otherContext = new ClientContext('whatever', 'whatever', null);
            if ($hosts->toArray() !== $otherContext->writeHostsArray->toArray()) {
                $isRandom = true;
                break;
            }
        }

        $this->assertTrue($isRandom);

        // Check that the first entry is the correct API endpoint.
        $this->assertEquals('whatever.algolia.net', $hosts[0]);

        // As hosts are in a random order, we sort everything to be sure the correct hosts are present.
        $hostsArray = $hosts->toArray();
        sort($hostsArray, SORT_STRING);

        $expectedHosts = array(
            'whatever-1.algolianet.com',
            'whatever-2.algolianet.com',
            'whatever-3.algolianet.com',
            'whatever.algolia.net',
        );
        $this->assertEquals($expectedHosts, $hostsArray);
    }
}

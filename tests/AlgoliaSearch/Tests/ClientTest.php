<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;
use AlgoliaSearch\FileFailingHostsCache;
use AlgoliaSearch\InMemoryFailingHostsCache;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresFailingHostsCacheIsOfCorrectType()
    {
        new Client('whatever', 'whatever', null, array(
            Client::FAILING_HOSTS_CACHE => 'incorrect_type',
        ));
    }

    public function testShouldForwardFailingHostsCacheToClientContext()
    {
        $cache = new InMemoryFailingHostsCache();

        $client = new Client('whatever', 'whatever', null, array(
            Client::FAILING_HOSTS_CACHE => $cache,
        ));

        $this->assertSame($cache, $client->getContext()->getFailingHostsCache());

        $cache = new FileFailingHostsCache();

        $client = new Client('whatever', 'whatever', null, array(
            Client::FAILING_HOSTS_CACHE => $cache,
        ));

        $this->assertSame($cache, $client->getContext()->getFailingHostsCache());
    }

    public function testCanLetClientContextChooseFailingHostsCache()
    {
        $client = new Client('whatever', 'whatever');
        $this->assertInstanceOf('\AlgoliaSearch\FailingHostsCache', $client->getContext()->getFailingHostsCache());
    }
}

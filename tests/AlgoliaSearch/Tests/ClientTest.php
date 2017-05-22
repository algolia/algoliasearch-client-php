<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Client;
use AlgoliaSearch\ClientContext;
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

    /**
     * @expectedException \AlgoliaSearch\AlgoliaConnectionException
     */
    public function testEnsuresFailingHostsExceptionCode()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $context = new ClientContext('whatever', 'whatever', null);

        $client->request($context, 'whatever', 'whatever', null, null, array(), 0, 0);
    }

    /**
     * @expectedException \AlgoliaSearch\AlgoliaException
     * @expectedExceptionCode 403
     */
    public function testClientError403WithBadClient()
    {
        $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $context = new ClientContext('whatever', 'whatever', null);

        $client->request(
            $context,
            'GET',
            '/1/indexes/',
            null,
            null,
            array(getenv('ALGOLIA_APPLICATION_ID') . '-dsn.algolia.net'),
            1,
            30
        );
    }
}

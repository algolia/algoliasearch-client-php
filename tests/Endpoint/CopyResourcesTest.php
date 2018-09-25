<?php

namespace Algolia\AlgoliaSearch\Tests\Endpoint;

use Algolia\AlgoliaSearch\Client;

class CopyResourcesTest extends RequestTestCase
{
    public function testCopySettings()
    {
        /** @var Client $client */
        $client = Client::get();
        list($request, $timeout, $connectTimeout) = $client->copySettings('src', 'dest');

        $this->assertEndpointEquals($request, '/1/indexes/src/operation');
        $this->assertBodySubset(array(
            'operation' => 'copy',
            'destination' => 'dest',
            'scope' => array('settings'),
        ),
            $request
        );
    }

    public function testCopySynonyms()
    {
        /** @var Client $client */
        $client = Client::get();
        list($request, $timeout, $connectTimeout) = $client->copySynonyms('src', 'dest');

        $this->assertEndpointEquals($request, '/1/indexes/src/operation');
        $this->assertBodySubset(array(
            'operation' => 'copy',
            'destination' => 'dest',
            'scope' => array('synonyms'),
        ),
            $request
        );
    }

    public function testCopyRules()
    {
        /** @var Client $client */
        $client = Client::get();
        list($request, $timeout, $connectTimeout) = $client->copyRules('src', 'dest');

        $this->assertEndpointEquals($request, '/1/indexes/src/operation');
        $this->assertBodySubset(array(
                'operation' => 'copy',
                'destination' => 'dest',
                'scope' => array('rules'),
            ),
            $request
        );
    }
}

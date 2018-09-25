<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Client;

class CopyResourcesTest extends RequestTestCase
{
    public function testCopySettings()
    {
        /** @var Client $client */
        $client = Client::get();
        $mockedResponse = $client->copySettings('src', 'dest');

        $this->assertEndpointEquals($mockedResponse['request'], '/1/indexes/src/operation');
        $this->assertBodySubset(array(
            'operation' => 'copy',
            'destination' => 'dest',
            'scope' => array('settings'),
        ),
            $mockedResponse['request']
        );
    }

    public function testCopySynonyms()
    {
        /** @var Client $client */
        $client = Client::get();
        $mockedResponse = $client->copySynonyms('src', 'dest');

        $this->assertEndpointEquals($mockedResponse['request'], '/1/indexes/src/operation');
        $this->assertBodySubset(array(
            'operation' => 'copy',
            'destination' => 'dest',
            'scope' => array('synonyms'),
        ),
            $mockedResponse['request']
        );
    }

    public function testCopyRules()
    {
        /** @var Client $client */
        $client = Client::get();
        $mockedResponse = $client->copyRules('src', 'dest');

        $this->assertEndpointEquals($mockedResponse['request'], '/1/indexes/src/operation');
        $this->assertBodySubset(array(
                'operation' => 'copy',
                'destination' => 'dest',
                'scope' => array('rules'),
            ),
            $mockedResponse['request']
        );
    }
}

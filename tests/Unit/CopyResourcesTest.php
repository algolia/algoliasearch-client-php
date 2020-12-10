<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\SearchClient;

class CopyResourcesTest extends RequestTestCase
{
    /** @var \Algolia\AlgoliaSearch\SearchClient */
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = SearchClient::create('id', 'key');
    }

    public function testCopySettings()
    {
        try {
            static::$client->copySettings('src', 'dest');
        } catch (RequestException $e) {
            $this->assertEndpointEquals($e->getRequest(), '/1/indexes/src/operation');
            $this->assertBodySubset(array(
                    'operation' => 'copy',
                    'destination' => 'dest',
                    'scope' => array('settings'),
                ),
                $e->getRequest()
            );
        }
    }

    public function testCopySynonyms()
    {
        try {
            static::$client->copySynonyms('src', 'dest');
        } catch (RequestException $e) {
            $this->assertEndpointEquals($e->getRequest(), '/1/indexes/src/operation');
            $this->assertBodySubset(array(
                    'operation' => 'copy',
                    'destination' => 'dest',
                    'scope' => array('synonyms'),
                ),
                $e->getRequest()
            );
        }
    }

    public function testCopyRules()
    {
        try {
            static::$client->copyRules('src', 'dest');
        } catch (RequestException $e) {
            $this->assertEndpointEquals($e->getRequest(), '/1/indexes/src/operation');
            $this->assertBodySubset(array(
                    'operation' => 'copy',
                    'destination' => 'dest',
                    'scope' => array('rules'),
                ),
                $e->getRequest()
            );
        }
    }
}

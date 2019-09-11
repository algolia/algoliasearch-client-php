<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\PlacesClient;

class SearchTest extends RequestTestCase
{
    public function testQueryAsNullValue()
    {
        $client = SearchClient::create('id', 'key');

        try {
            $client->searchUserIds(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsSet('Content-Length', $e->getRequest());
            $this->assertEncodedBodySubset(array('query' => ''),
                $e->getRequest());
        }

        $index = $client->initIndex('foo');

        try {
            $index->search(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsSet('Content-Length', $e->getRequest());
            $this->assertEncodedBodySubset(array('query' => ''),
                $e->getRequest());
        }

        try {
            $index->searchSynonyms(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsSet('Content-Length', $e->getRequest());
            $this->assertEncodedBodySubset(array('query' => ''),
                $e->getRequest());
        }

        try {
            $index->searchRules(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsSet('Content-Length', $e->getRequest());
            $this->assertEncodedBodySubset(array('query' => ''),
                $e->getRequest());
        }

        try {
            $index->searchRules(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsSet('Content-Length', $e->getRequest());
            $this->assertEncodedBodySubset(array('query' => ''),
                $e->getRequest());
        }

        $client = PlacesClient::create('id', 'key');

        try {
            $client->search(null);
        } catch (RequestException $e) {
            $this->assertHeaderIsNotSet('Content-Encoding', $e->getRequest());
            $this->assertHeaderIsNotSet('Content-Length', $e->getRequest());
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }
    }
}

<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\PlacesClient;
use Algolia\AlgoliaSearch\SearchClient;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class SearchTest extends RequestTestCase
{
    use ArraySubsetAsserts;

    public function testQueryAsNullValue()
    {
        $client = SearchClient::create('id', 'key');

        try {
            $client->searchUserIds(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }

        $index = $client->initIndex('foo');

        try {
            $index->search(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }

        try {
            $index->searchSynonyms(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }

        try {
            $index->searchRules(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }

        try {
            $index->searchRules(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }

        $client = PlacesClient::create('id', 'key');

        try {
            $client->search(null);
        } catch (RequestException $e) {
            $this->assertBodySubset(array('query' => ''), $e->getRequest());
        }
    }
}

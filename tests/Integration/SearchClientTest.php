<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchClient;
use PHPUnit\Framework\TestCase;

class SearchClientTest extends TestCase
{
    public function testGetSecuredApiKeyRemainingValidity()
    {
        $now = time();

        $newSecuredKey = SearchClient::generateSecuredApiKey('foo',
            array('validUntil' => $now - (10 * 60))
        );

        $this->assertLessThan(0, SearchClient::getSecuredApiKeyRemainingValidity($newSecuredKey));

        $newSecuredKey = SearchClient::generateSecuredApiKey('foo',
            array('validUntil' => $now + (10 * 60))
        );

        $this->assertGreaterThan(0, SearchClient::getSecuredApiKeyRemainingValidity($newSecuredKey));

        try {
            $newSecuredKey = SearchClient::generateSecuredApiKey('foo', array());
            SearchClient::getSecuredApiKeyRemainingValidity($newSecuredKey);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ValidUntilNotFoundException', $e);
        }
    }
}

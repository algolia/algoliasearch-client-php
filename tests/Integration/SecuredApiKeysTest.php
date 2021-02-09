<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class SecuredApiKeysTest extends BaseTest
{
    public function testSecuredApiKeys()
    {
        static::$indexes['secured_api_keys'] = TestHelper::getTestIndexName('secured_api_keys');
        static::$indexes['secured_api_keys_dev'] = TestHelper::getTestIndexName('secured_api_keys_dev');

        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex(static::$indexes['secured_api_keys']);

        /** @var SearchIndex $indexDev */
        $indexDev = TestHelper::getClient()->initIndex(static::$indexes['secured_api_keys_dev']);

        $responses = array();

        $object = array('objectID' => 'one');

        $responses[] = $index->saveObject($object, array('autoGenerateObjectIDIfNotExist' => true));
        $responses[] = $indexDev->saveObject($object, array('autoGenerateObjectIDIfNotExist' => true));

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $securedApiKey = SearchClient::generateSecuredApiKey(
            getenv('ALGOLIA_SEARCH_KEY_1'),
            array(
                'validUntil' => time() + 600,
                'restrictIndices' => static::$indexes['secured_api_keys'],
            )
        );

        $securedConfig = array(
            'appId' => getenv('ALGOLIA_APPLICATION_ID_1'),
            'apiKey' => $securedApiKey,
        );

        $securedClient = TestHelper::getClient($securedConfig);

        /** @var SearchIndex $securedIndex */
        $securedIndex = $securedClient->initIndex(static::$indexes['secured_api_keys']);

        /** @var SearchIndex $securedIndexDev */
        $securedIndexDev = $securedClient->initIndex(static::$indexes['secured_api_keys_dev']);

        $res = $securedIndex->search('');
        $this->assertCount(1, $res['hits']);

        try {
            $res = $securedIndexDev->search('');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\BadRequestException', $e);
            $this->assertEquals(403, $e->getCode());
            $this->assertEquals('Index not allowed with this API key', $e->getMessage());
        }
    }

    public function testExpiredSecuredApiKeys()
    {
        $securedApiKey = SearchClient::generateSecuredApiKey(
            getenv('ALGOLIA_SEARCH_KEY_1'),
            array(
                'validUntil' => time() + 600,
            )
        );

        $this->assertGreaterThan(0, SearchClient::getSecuredApiKeyRemainingValidity($securedApiKey));

        $securedApiKey = SearchClient::generateSecuredApiKey(
            getenv('ALGOLIA_SEARCH_KEY_1'),
            array(
                'validUntil' => time() - 600,
            )
        );

        $this->assertLessThan(0, SearchClient::getSecuredApiKeyRemainingValidity($securedApiKey));
    }
}

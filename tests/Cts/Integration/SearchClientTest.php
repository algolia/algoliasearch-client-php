<?php

namespace Algolia\AlgoliaSearch\Tests\Cts\Integration;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\Cts\TestHelper;

class SearchClientTest extends BaseTest
{
    protected static $apiKeys = array();

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (count(self::$apiKeys) > 0) {
            foreach (self::$apiKeys as $apiKey) {
                TestHelper::getClient()->deleteApiKey($apiKey)->wait();
            }
            self::$apiKeys = array();
        }
    }

    public function testCopyIndex()
    {
        static::$indexes['copy_index'] = TestHelper::getTestIndexName('copy_index');

        /** @var SearchIndex $copyIndex */
        $copyIndex = TestHelper::getClient()->initIndex(static::$indexes['copy_index']);

        $responses = array();

        $figures = array(
            array('objectID' => 'one', 'company' => 'apple'),
            array('objectID' => 'two', 'company' => 'algolia'),
        );

        $responses[] = $copyIndex->saveObjects($figures, array('autoGenerateObjectIDIfNotExist' => true));

        $settings = array(
            'attributesForFaceting' => array('company')
        );

        $responses[] = $copyIndex->setSettings($settings);

        $synonym = array(
            'objectID' => 'google_placeholder',
            'type' => 'placeholder',
            'placeholder' => '<GOOG>',
            'replacements' => array('Google', 'GOOG'),
        );

        $responses[] = $copyIndex->saveSynonym($synonym);

        $rule = array(
            'objectID' => 'company_auto_faceting',
            'conditions' => array(
                array(
                    'anchoring' => 'contains',
                    'pattern' => '{facet:company}',
                ),
            ),
            'consequence' => array(
                'params' => array(
                    'automaticFacetFilters' => array('company')
                ),
            ),
        );

        $responses[] = $copyIndex->saveRule($rule);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        static::$indexes['copy_index_settings'] = TestHelper::getTestIndexName('copy_index_settings');
        static::$indexes['copy_index_rules'] = TestHelper::getTestIndexName('copy_index_rules');
        static::$indexes['copy_index_synonyms'] = TestHelper::getTestIndexName('copy_index_synonyms');
        static::$indexes['copy_index_full_copy'] = TestHelper::getTestIndexName('copy_index_full_copy');

        /** @var SearchIndex $copyIndexSettings */
        $copyIndexSettings = TestHelper::getClient()->initIndex(static::$indexes['copy_index_settings']);

        /** @var SearchIndex $copyIndexRules */
        $copyIndexRules = TestHelper::getClient()->initIndex(static::$indexes['copy_index_rules']);

        /** @var SearchIndex $copyIndexSynonyms */
        $copyIndexSynonyms = TestHelper::getClient()->initIndex(static::$indexes['copy_index_synonyms']);

        /** @var SearchIndex $copyIndexFull */
        $copyIndexFull = TestHelper::getClient()->initIndex(static::$indexes['copy_index_full_copy']);

        $responses[] = TestHelper::getClient()->copySettings(
            static::$indexes['copy_index'],
            static::$indexes['copy_index_settings']
        );
        $responses[] = TestHelper::getClient()->copyRules(
            static::$indexes['copy_index'],
            static::$indexes['copy_index_rules']
        );
        $responses[] = TestHelper::getClient()->copySynonyms(
            static::$indexes['copy_index'],
            static::$indexes['copy_index_synonyms']
        );
        $responses[] = TestHelper::getClient()->copyIndex(
            static::$indexes['copy_index'],
            static::$indexes['copy_index_full_copy']
        );

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        self::assertEquals($copyIndex->getSettings(), $copyIndexSettings->getSettings());
        self::assertEquals($copyIndex->getRule($rule['objectID']), $copyIndexRules->getRule($rule['objectID']));
        self::assertEquals(
            $copyIndex->getSynonym($synonym['objectID']),
            $copyIndexSynonyms->getSynonym($synonym['objectID'])
        );

        self::assertEquals($copyIndex->getSettings(), $copyIndexFull->getSettings());
        self::assertEquals($copyIndex->getRule($rule['objectID']), $copyIndexFull->getRule($rule['objectID']));
        self::assertEquals(
            $copyIndex->getSynonym($synonym['objectID']),
            $copyIndexFull->getSynonym($synonym['objectID'])
        );
    }

    public function testMcm()
    {
        // @todo
        self::assertEquals(1,1);
    }

    public function testApiKeys()
    {
        $acl = array('search');

        $params = array(
            'description' => 'A description',
            'indexes' => array('index'),
            'maxHitsPerQuery' => 1000,
            'maxQueriesPerIPPerHour' => 1000,
            'queryParameters' => 'typoTolerance=strict',
            'referers' => array('referer'),
            'validity' => 600,
        );

        $res = TestHelper::getClient()->addApiKey($acl, $params)->wait();
        self::$apiKeys[] = $res['key'];

        $apiKey = TestHelper::getClient()->getApiKey($res['key']);

        self::assertEquals($acl, $apiKey['acl']);
        self::assertEquals($params['description'], $apiKey['description']);

        $allApiKeys = TestHelper::getClient()->listApiKeys();
        $fetchedApiKeyValues = array();

        foreach ($allApiKeys['keys'] as $fetchedApiKey) {
            $fetchedApiKeyValues[] = $fetchedApiKey['value'];
        }

        self::assertContains($apiKey['value'], $fetchedApiKeyValues);

        $newParams = $params;
        $newParams['acl'] = array('search');
        $newParams['maxHitsPerQuery'] = 42;

        TestHelper::getClient()->updateApiKey($res['key'], $newParams)->wait();

        $retry = 1;
        $time = 100000;
        $maxRetries = 100;
        do {
            if ($retry >= $maxRetries) {
                break;
            }

            try {
                $updatedApiKey = TestHelper::getClient()->getApiKey($res['key']);

                if ($updatedApiKey['maxHitsPerQuery'] !==  $apiKey['maxHitsPerQuery']) {
                    self::assertEquals(42, $updatedApiKey['maxHitsPerQuery']);
                    break;
                }
            } catch (NotFoundException $e) {
                // Try again
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while (true);

        TestHelper::getClient()->deleteApiKey($res['key']);

        try {
            TestHelper::getClient()->getApiKey($res['key']);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        TestHelper::getClient()->restoreApiKey($res['key'])->wait();

        $restoredApiKey = TestHelper::getClient()->getApiKey($res['key']);
        self::assertEquals($acl, $restoredApiKey['acl']);
        self::assertEquals($params['description'], $restoredApiKey['description']);

        TestHelper::getClient()->deleteApiKey($res['key']);
    }

    public function testLogs()
    {
        TestHelper::getClient()->listIndices();
        TestHelper::getClient()->listIndices();

        $params = array(
          'length' => 2,
          'offset' => 0,
          'type' => 'all',
        );

        $res = TestHelper::getClient()->getLogs($params);
        self::assertCount(2, $res['logs']);
    }

    public function testMultipleOperations()
    {
        static::$indexes['multiple_operations'] = TestHelper::getTestIndexName('multiple_operations');
        static::$indexes['multiple_operations_dev'] = TestHelper::getTestIndexName('multiple_operations_dev');

        $index1 = static::$indexes['multiple_operations'];
        $index2 = static::$indexes['multiple_operations_dev'];

        /** @var SearchIndex $operationsIndex */
        $operationsIndex = TestHelper::getClient()->initIndex($index1);

        /** @var SearchIndex $operationsDevIndex */
        $operationsDevIndex = TestHelper::getClient()->initIndex($index2);

        $batch = array(
            array('indexName' => $index1, 'action' => 'addObject', 'body' => array('firstname' => 'Jimmie')),
            array('indexName' => $index1, 'action' => 'addObject', 'body' => array('firstname' => 'Jimmie')),
            array('indexName' => $index2, 'action' => 'addObject', 'body' => array('firstname' => 'Jimmie')),
            array('indexName' => $index2, 'action' => 'addObject', 'body' => array('firstname' => 'Jimmie')),
        );

        $res = TestHelper::getClient()->multipleBatch($batch);
        $objectIds = $res['objectIDs'];

        $res = TestHelper::getClient()->multipleGetObjects(
            array(
                array('indexName' => $index1, 'objectID' => $objectIds[0]),
                array('indexName' => $index1, 'objectID' => $objectIds[1]),
                array('indexName' => $index2, 'objectID' => $objectIds[2]),
                array('indexName' => $index2, 'objectID' => $objectIds[3]),
            )
        );

        $objects = $res['results'];

        self::assertEquals($objectIds[0], $objects[0]['objectID']);
        self::assertEquals($objectIds[1], $objects[1]['objectID']);
        self::assertEquals($objectIds[2], $objects[2]['objectID']);
        self::assertEquals($objectIds[3], $objects[3]['objectID']);

        $res = TestHelper::getClient()->multipleQueries(
            array(
                array(
                    'indexName' => $index1,
                    'params' => http_build_query(array('query' => '', 'hitsPerPage' => 2))
                ),
                array(
                    'indexName' => $index2,
                    'params' => http_build_query(array('query' => '', 'hitsPerPage' => 2))
                ),
            ),
            array('strategy' => 'none')
        );

        $results = $res['results'];

        self::assertCount(2, $results);
        self::assertCount(2, $results[0]['hits']);
        self::assertEquals(2, $results[0]['nbHits']);
        self::assertCount(2, $results[1]['hits']);
        self::assertEquals(2, $results[1]['nbHits']);

        $res = TestHelper::getClient()->multipleQueries(
            array(
                array(
                    'indexName' => $index1,
                    'params' => http_build_query(array('query' => '', 'hitsPerPage' => 2))
                ),
                array(
                    'indexName' => $index2,
                    'params' => http_build_query(array('query' => '', 'hitsPerPage' => 2))
                ),
            ),
            array('strategy' => 'stopIfEnoughMatches')
        );

        $results = $res['results'];

        self::assertCount(2, $results);
        self::assertCount(2, $results[0]['hits']);
        self::assertEquals(2, $results[0]['nbHits']);
        self::assertCount(0, $results[1]['hits']);
        self::assertEquals(0, $results[1]['nbHits']);
    }

}

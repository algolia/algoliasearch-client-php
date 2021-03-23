<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class SearchClientTest extends BaseTest
{
    protected static $apiKeys = [];

    /** @var SearchClient */
    private $mcmClient;

    /** @var string */
    private $mcmUserId0;

    /** @var string */
    private $mcmUserId1;

    /** @var string */
    private $mcmUserId2;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (count(self::$apiKeys) > 0) {
            foreach (self::$apiKeys as $apiKey) {
                TestHelper::getClient()->deleteApiKey($apiKey)->wait();
            }
            self::$apiKeys = [];
        }
    }

    public function testCopyIndex()
    {
        $this->indexes['copy_index'] = TestHelper::getTestIndexName('copy_index');

        /** @var SearchIndex $copyIndex */
        $copyIndex = TestHelper::getClient()->initIndex($this->indexes['copy_index']);

        $responses = [];

        $figures = [
            ['objectID' => 'one', 'company' => 'apple'],
            ['objectID' => 'two', 'company' => 'algolia'],
        ];

        $responses[] = $copyIndex->saveObjects($figures, ['autoGenerateObjectIDIfNotExist' => true]);

        $settings = [
            'attributesForFaceting' => ['company'],
        ];

        $responses[] = $copyIndex->setSettings($settings);

        $synonym = [
            'objectID' => 'google_placeholder',
            'type' => 'placeholder',
            'placeholder' => '<GOOG>',
            'replacements' => ['Google', 'GOOG'],
        ];

        $responses[] = $copyIndex->saveSynonym($synonym);

        $rule = [
            'objectID' => 'company_auto_faceting',
            'conditions' => [
                [
                    'anchoring' => 'contains',
                    'pattern' => '{facet:company}',
                ],
            ],
            'consequence' => [
                'params' => [
                    'automaticFacetFilters' => ['company'],
                ],
            ],
        ];

        $responses[] = $copyIndex->saveRule($rule);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $this->indexes['copy_index_settings'] = TestHelper::getTestIndexName('copy_index_settings');
        $this->indexes['copy_index_rules'] = TestHelper::getTestIndexName('copy_index_rules');
        $this->indexes['copy_index_synonyms'] = TestHelper::getTestIndexName('copy_index_synonyms');
        $this->indexes['copy_index_full_copy'] = TestHelper::getTestIndexName('copy_index_full_copy');

        /** @var SearchIndex $copyIndexSettings */
        $copyIndexSettings = TestHelper::getClient()->initIndex($this->indexes['copy_index_settings']);

        /** @var SearchIndex $copyIndexRules */
        $copyIndexRules = TestHelper::getClient()->initIndex($this->indexes['copy_index_rules']);

        /** @var SearchIndex $copyIndexSynonyms */
        $copyIndexSynonyms = TestHelper::getClient()->initIndex($this->indexes['copy_index_synonyms']);

        /** @var SearchIndex $copyIndexFull */
        $copyIndexFull = TestHelper::getClient()->initIndex($this->indexes['copy_index_full_copy']);

        $responses[] = TestHelper::getClient()->copySettings(
            $this->indexes['copy_index'],
            $this->indexes['copy_index_settings']
        );
        $responses[] = TestHelper::getClient()->copyRules(
            $this->indexes['copy_index'],
            $this->indexes['copy_index_rules']
        );
        $responses[] = TestHelper::getClient()->copySynonyms(
            $this->indexes['copy_index'],
            $this->indexes['copy_index_synonyms']
        );
        $responses[] = TestHelper::getClient()->copyIndex(
            $this->indexes['copy_index'],
            $this->indexes['copy_index_full_copy']
        );

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $this->assertEquals($copyIndex->getSettings(), $copyIndexSettings->getSettings());
        $this->assertEquals($copyIndex->getRule($rule['objectID']), $copyIndexRules->getRule($rule['objectID']));
        $this->assertEquals(
            $copyIndex->getSynonym($synonym['objectID']),
            $copyIndexSynonyms->getSynonym($synonym['objectID'])
        );

        $this->assertEquals($copyIndex->getSettings(), $copyIndexFull->getSettings());
        $this->assertEquals($copyIndex->getRule($rule['objectID']), $copyIndexFull->getRule($rule['objectID']));
        $this->assertEquals(
            $copyIndex->getSynonym($synonym['objectID']),
            $copyIndexFull->getSynonym($synonym['objectID'])
        );
    }

    public function testMcm()
    {
        $config = [
            'appId' => getenv('ALGOLIA_APPLICATION_ID_MCM'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_MCM'),
        ];

        $this->mcmClient = TestHelper::getClient($config);
        $clusterList = $this->mcmClient->listClusters();

        $this->assertCount(2, $clusterList['clusters']);

        $clusterName = $clusterList['clusters'][0]['clusterName'];

        $this->mcmUserId0 = TestHelper::getTestUserName('0');
        $this->mcmUserId1 = TestHelper::getTestUserName('1');
        $this->mcmUserId2 = TestHelper::getTestUserName('2');

        $response = $this->mcmClient->assignUserId($this->mcmUserId0, $clusterName);
        $this->assertArrayHasKey('createdAt', $response);

        $response = $this->autoRetryGetUserId($this->mcmUserId0);
        $this->assertEquals($response['userID'], $this->mcmUserId0);
        $this->assertEquals($response['clusterName'], $clusterName);

        $response = $this->mcmClient->assignUserIds([$this->mcmUserId1, $this->mcmUserId2], $clusterName);
        $this->assertArrayHasKey('createdAt', $response);

        $response = $this->autoRetryGetUserId($this->mcmUserId1);
        $this->assertEquals($response['userID'], $this->mcmUserId1);
        $this->assertEquals($response['clusterName'], $clusterName);

        $response = $this->autoRetryGetUserId($this->mcmUserId2);
        $this->assertEquals($response['userID'], $this->mcmUserId2);
        $this->assertEquals($response['clusterName'], $clusterName);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId0);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId0);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId1);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId1);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId2);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId2);

        $response = $this->mcmClient->listUserIds();
        $this->assertNotEmpty($response['userIDs']);

        $result0 = '';
        foreach ($response['userIDs'] as $userIDset) {
            if ($userIDset['userID'] === $this->mcmUserId0) {
                $result0 = $userIDset['userID'];
            }
        }
        $this->assertEquals($result0, $this->mcmUserId0);

        $response = $this->mcmClient->getTopUserId();
        $topUser = $response['topUsers'][$clusterName][0];
        $this->assertArrayHasKey('userID', $topUser);
        $this->assertArrayHasKey('nbRecords', $topUser);
        $this->assertArrayHasKey('dataSize', $topUser);

        $response = $this->mcmClient->getTopUserIds();
        $topUser = $response['topUsers'][$clusterName][0];
        $this->assertArrayHasKey('userID', $topUser);
        $this->assertArrayHasKey('nbRecords', $topUser);
        $this->assertArrayHasKey('dataSize', $topUser);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId0);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId1);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId2);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->mcmClient->hasPendingMappings(['retrieveMappings' => true]);
        $this->assertArrayHasKey('clusters', $response);
        $this->assertArrayHasKey('pending', $response);

        $response = $this->mcmClient->hasPendingMappings(['getClusters' => true]);
        $this->assertArrayHasKey('clusters', $response);
        $this->assertArrayHasKey('pending', $response);

        $response = $this->mcmClient->hasPendingMappings();
        $this->assertArrayNotHasKey('clusters', $response);
        $this->assertArrayHasKey('pending', $response);
    }

    public function testApiKeys()
    {
        $acl = ['search'];

        $params = [
            'description' => 'A description',
            'indexes' => [TestHelper::getTestIndexName('index_api_keys')],
            'maxHitsPerQuery' => 1000,
            'maxQueriesPerIPPerHour' => 1000,
            'queryParameters' => 'typoTolerance=strict',
            'referers' => ['referer'],
            'validity' => 600,
        ];

        $res = TestHelper::getClient()->addApiKey($acl, $params)->wait();
        self::$apiKeys[] = $res['key'];

        $apiKey = TestHelper::getClient()->getApiKey($res['key']);

        $this->assertEquals($acl, $apiKey['acl']);
        $this->assertEquals($params['description'], $apiKey['description']);

        $allApiKeys = TestHelper::getClient()->listApiKeys();
        $fetchedApiKeyValues = [];

        foreach ($allApiKeys['keys'] as $fetchedApiKey) {
            $fetchedApiKeyValues[] = $fetchedApiKey['value'];
        }

        $this->assertContains($apiKey['value'], $fetchedApiKeyValues);

        $newParams = $params;
        $newParams['acl'] = ['search'];
        $newParams['maxHitsPerQuery'] = 42;

        TestHelper::getClient()->updateApiKey($res['key'], $newParams)->wait();

        $updatedApiKey = TestHelper::retry(function () use ($res) {
            return TestHelper::getClient()->getApiKey($res['key']);
        });

        if ($updatedApiKey['maxHitsPerQuery'] !== $apiKey['maxHitsPerQuery']) {
            $this->assertEquals(42, $updatedApiKey['maxHitsPerQuery']);
        }

        TestHelper::getClient()->deleteApiKey($res['key'])->wait();

        try {
            TestHelper::getClient()->getApiKey($res['key']);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        TestHelper::retry(function () use ($res) {
            TestHelper::getClient()->restoreApiKey($res['key'])->wait();
        });

        $restoredApiKey = TestHelper::retry(function () use ($res) {
            return TestHelper::getClient()->getApiKey($res['key']);
        });

        $this->assertEquals($acl, $restoredApiKey['acl']);
        $this->assertEquals($params['description'], $restoredApiKey['description']);

        TestHelper::getClient()->deleteApiKey($res['key'])->wait();
    }

    public function testLogs()
    {
        TestHelper::getClient()->listIndices();
        TestHelper::getClient()->listIndices();

        $params = [
          'length' => 2,
          'offset' => 0,
          'type' => 'all',
        ];

        $res = TestHelper::getClient()->getLogs($params);
        $this->assertCount(2, $res['logs']);
    }

    public function testMultipleOperations()
    {
        $this->indexes['multiple_operations'] = TestHelper::getTestIndexName('multiple_operations');
        $this->indexes['multiple_operations_dev'] = TestHelper::getTestIndexName('multiple_operations_dev');

        $index1 = $this->indexes['multiple_operations'];
        $index2 = $this->indexes['multiple_operations_dev'];

        /** @var SearchIndex $operationsIndex */
        $operationsIndex = TestHelper::getClient()->initIndex($index1);

        /** @var SearchIndex $operationsDevIndex */
        $operationsDevIndex = TestHelper::getClient()->initIndex($index2);

        $batch = [
            ['indexName' => $index1, 'action' => 'addObject', 'body' => ['firstname' => 'Jimmie']],
            ['indexName' => $index1, 'action' => 'addObject', 'body' => ['firstname' => 'Jimmie']],
            ['indexName' => $index2, 'action' => 'addObject', 'body' => ['firstname' => 'Jimmie']],
            ['indexName' => $index2, 'action' => 'addObject', 'body' => ['firstname' => 'Jimmie']],
        ];

        $res = TestHelper::getClient()->multipleBatch($batch)->wait();
        $objectIds = $res['objectIDs'];

        $res = TestHelper::getClient()->multipleGetObjects(
            [
                ['indexName' => $index1, 'objectID' => $objectIds[0]],
                ['indexName' => $index1, 'objectID' => $objectIds[1]],
                ['indexName' => $index2, 'objectID' => $objectIds[2]],
                ['indexName' => $index2, 'objectID' => $objectIds[3]],
            ]
        );

        $objects = $res['results'];

        $this->assertEquals($objectIds[0], $objects[0]['objectID']);
        $this->assertEquals($objectIds[1], $objects[1]['objectID']);
        $this->assertEquals($objectIds[2], $objects[2]['objectID']);
        $this->assertEquals($objectIds[3], $objects[3]['objectID']);

        $res = TestHelper::getClient()->multipleQueries(
            [
                [
                    'indexName' => $index1,
                    'params' => http_build_query(['query' => '', 'hitsPerPage' => 2]),
                ],
                [
                    'indexName' => $index2,
                    'params' => http_build_query(['query' => '', 'hitsPerPage' => 2]),
                ],
            ],
            ['strategy' => 'none']
        );

        $results = $res['results'];

        $this->assertCount(2, $results);
        $this->assertCount(2, $results[0]['hits']);
        $this->assertEquals(2, $results[0]['nbHits']);
        $this->assertCount(2, $results[1]['hits']);
        $this->assertEquals(2, $results[1]['nbHits']);

        $res = TestHelper::getClient()->multipleQueries(
            [
                [
                    'indexName' => $index1,
                    'params' => http_build_query(['query' => '', 'hitsPerPage' => 2]),
                ],
                [
                    'indexName' => $index2,
                    'params' => http_build_query(['query' => '', 'hitsPerPage' => 2]),
                ],
            ],
            ['strategy' => 'stopIfEnoughMatches']
        );

        $results = $res['results'];

        $this->assertCount(2, $results);
        $this->assertCount(2, $results[0]['hits']);
        $this->assertEquals(2, $results[0]['nbHits']);
        $this->assertCount(0, $results[1]['hits']);
        $this->assertEquals(0, $results[1]['nbHits']);
    }

    private function autoRetryGetUserId($userID)
    {
        $retry = 0;
        $response = [];
        do {
            try {
                $response = $this->mcmClient->getUserId($userID);
            } catch (\Exception $e) {
                sleep(1);
                $retry++;
            }
        } while (!array_key_exists('userID', $response) && $retry < 100);

        return $response;
    }

    private function autoRetryRemoveUserId($userID)
    {
        $retry = 0;
        $response = [];
        do {
            try {
                $response = $this->mcmClient->removeUserId($userID);
            } catch (\Exception $e) {
                sleep(1);
                $retry++;
            }
        } while (!array_key_exists('deletedAt', $response) && $retry < 100);

        return $response;
    }
}

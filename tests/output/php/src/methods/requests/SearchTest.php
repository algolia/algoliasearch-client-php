<?php

namespace Algolia\AlgoliaSearch\Test\Api;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Configuration\SearchConfig;
use Algolia\AlgoliaSearch\Http\HttpClientInterface;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * SearchTest
 *
 * @category Class
 * @package  Algolia\AlgoliaSearch
 */
class SearchTest extends TestCase implements HttpClientInterface
{
    /**
     * @var RequestInterface[]
     */
    private $recordedRequests = [];

    protected function assertRequests(array $requests)
    {
        $this->assertGreaterThan(0, count($requests));
        $this->assertEquals(count($requests), count($this->recordedRequests));

        foreach ($requests as $i => $request) {
            $recordedRequest = $this->recordedRequests[$i];

            $this->assertEquals(
                $request['method'],
                $recordedRequest->getMethod()
            );

            $this->assertEquals(
                $request['path'],
                $recordedRequest->getUri()->getPath()
            );

            if (isset($request['body'])) {
                $this->assertEquals(
                    json_encode($request['body']),
                    $recordedRequest->getBody()->getContents()
                );
            }
        }
    }

    public function sendRequest(
        RequestInterface $request,
        $timeout,
        $connectTimeout
    ) {
        $this->recordedRequests[] = $request;

        return new Response(200, [], '{}');
    }

    protected function getClient()
    {
        $api = new ApiWrapper(
            $this,
            SearchConfig::create(
                getenv('ALGOLIA_APP_ID'),
                getenv('ALGOLIA_API_KEY')
            ),
            ClusterHosts::create('127.0.0.1')
        );
        $config = SearchConfig::create('foo', 'bar');

        return new SearchClient($api, $config);
    }

    /**
     * Test case for AddApiKey
     * addApiKey
     */
    public function testAddApiKey0()
    {
        $client = $this->getClient();

        $client->addApiKey([
            'acl' => ['search', 'addObject'],

            'description' => 'my new api key',

            'validity' => 300,

            'maxQueriesPerIPPerHour' => 100,

            'maxHitsPerQuery' => 20,
        ]);

        $this->assertRequests([
            [
                'path' => '/1/keys',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"acl\":[\"search\",\"addObject\"],\"description\":\"my new api key\",\"validity\":300,\"maxQueriesPerIPPerHour\":100,\"maxHitsPerQuery\":20}"
                ),
            ],
        ]);
    }

    /**
     * Test case for AddOrUpdateObject
     * addOrUpdateObject
     */
    public function testAddOrUpdateObject0()
    {
        $client = $this->getClient();

        $client->addOrUpdateObject(
            'indexName',
            'uniqueID',
            ['key' => 'value']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/uniqueID',
                'method' => 'PUT',
                'body' => json_decode("{\"key\":\"value\"}"),
            ],
        ]);
    }

    /**
     * Test case for AppendSource
     * appendSource
     */
    public function testAppendSource0()
    {
        $client = $this->getClient();

        $client->appendSource([
            'source' => 'theSource',

            'description' => 'theDescription',
        ]);

        $this->assertRequests([
            [
                'path' => '/1/security/sources/append',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"source\":\"theSource\",\"description\":\"theDescription\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for AssignUserId
     * assignUserId
     */
    public function testAssignUserId0()
    {
        $client = $this->getClient();

        $client->assignUserId(
            'userID',
            ['cluster' => 'theCluster']
        );

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping',
                'method' => 'POST',
                'body' => json_decode("{\"cluster\":\"theCluster\"}"),
                'searchParams' => json_decode(
                    "{\"X-Algolia-User-ID\":\"userID\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Batch
     * batch
     */
    public function testBatch0()
    {
        $client = $this->getClient();

        $client->batch(
            'theIndexName',
            [
                'requests' => [
                    ['action' => 'delete', 'body' => ['key' => 'value']],
                ],
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"requests\":[{\"action\":\"delete\",\"body\":{\"key\":\"value\"}}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for BatchAssignUserIds
     * batchAssignUserIds
     */
    public function testBatchAssignUserIds0()
    {
        $client = $this->getClient();

        $client->batchAssignUserIds(
            'userID',
            ['cluster' => 'theCluster', 'users' => ['user1', 'user2']]
        );

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"cluster\":\"theCluster\",\"users\":[\"user1\",\"user2\"]}"
                ),
                'searchParams' => json_decode(
                    "{\"X-Algolia-User-ID\":\"userID\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for BatchDictionaryEntries
     * get batchDictionaryEntries results with minimal parameters
     */
    public function testBatchDictionaryEntries0()
    {
        $client = $this->getClient();

        $client->batchDictionaryEntries(
            'compounds',
            [
                'requests' => [
                    [
                        'action' => 'addEntry',

                        'body' => ['objectID' => '1', 'language' => 'en'],
                    ],

                    [
                        'action' => 'deleteEntry',

                        'body' => ['objectID' => '2', 'language' => 'fr'],
                    ],
                ],
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/compounds/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"requests\":[{\"action\":\"addEntry\",\"body\":{\"objectID\":\"1\",\"language\":\"en\"}},{\"action\":\"deleteEntry\",\"body\":{\"objectID\":\"2\",\"language\":\"fr\"}}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for BatchDictionaryEntries
     * get batchDictionaryEntries results with all parameters
     */
    public function testBatchDictionaryEntries1()
    {
        $client = $this->getClient();

        $client->batchDictionaryEntries(
            'compounds',
            [
                'clearExistingDictionaryEntries' => false,

                'requests' => [
                    [
                        'action' => 'addEntry',

                        'body' => [
                            'objectID' => '1',

                            'language' => 'en',

                            'word' => 'fancy',

                            'words' => ['believe', 'algolia'],

                            'decomposition' => ['trust', 'algolia'],

                            'state' => 'enabled',
                        ],
                    ],

                    [
                        'action' => 'deleteEntry',

                        'body' => [
                            'objectID' => '2',

                            'language' => 'fr',

                            'word' => 'humility',

                            'words' => ['candor', 'algolia'],

                            'decomposition' => ['grit', 'algolia'],

                            'state' => 'enabled',
                        ],
                    ],
                ],
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/compounds/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"clearExistingDictionaryEntries\":false,\"requests\":[{\"action\":\"addEntry\",\"body\":{\"objectID\":\"1\",\"language\":\"en\",\"word\":\"fancy\",\"words\":[\"believe\",\"algolia\"],\"decomposition\":[\"trust\",\"algolia\"],\"state\":\"enabled\"}},{\"action\":\"deleteEntry\",\"body\":{\"objectID\":\"2\",\"language\":\"fr\",\"word\":\"humility\",\"words\":[\"candor\",\"algolia\"],\"decomposition\":[\"grit\",\"algolia\"],\"state\":\"enabled\"}}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for BatchRules
     * batchRules
     */
    public function testBatchRules0()
    {
        $client = $this->getClient();

        $client->batchRules(
            'indexName',
            [
                [
                    'objectID' => 'a-rule-id',

                    'conditions' => [
                        ['pattern' => 'smartphone', 'anchoring' => 'contains'],
                    ],

                    'consequence' => [
                        'params' => ['filters' => 'category:smartphone'],
                    ],
                ],

                [
                    'objectID' => 'a-second-rule-id',

                    'conditions' => [
                        ['pattern' => 'apple', 'anchoring' => 'contains'],
                    ],

                    'consequence' => ['params' => ['filters' => 'brand:apple']],
                ],
            ],
            true,
            true
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "[{\"objectID\":\"a-rule-id\",\"conditions\":[{\"pattern\":\"smartphone\",\"anchoring\":\"contains\"}],\"consequence\":{\"params\":{\"filters\":\"category:smartphone\"}}},{\"objectID\":\"a-second-rule-id\",\"conditions\":[{\"pattern\":\"apple\",\"anchoring\":\"contains\"}],\"consequence\":{\"params\":{\"filters\":\"brand:apple\"}}}]"
                ),
                'searchParams' => json_decode(
                    "{\"forwardToReplicas\":\"true\",\"clearExistingRules\":\"true\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Browse
     * get browse results with minimal parameters
     */
    public function testBrowse0()
    {
        $client = $this->getClient();

        $client->browse('indexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/browse',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for Browse
     * get browse results with all parameters
     */
    public function testBrowse1()
    {
        $client = $this->getClient();

        $client->browse(
            'indexName',
            ['params' => "query=foo&facetFilters=['bar']", 'cursor' => 'cts']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/browse',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"params\":\"query=foo&facetFilters=['bar']\",\"cursor\":\"cts\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for ClearAllSynonyms
     * clearAllSynonyms
     */
    public function testClearAllSynonyms0()
    {
        $client = $this->getClient();

        $client->clearAllSynonyms('indexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/clear',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for ClearObjects
     * clearObjects
     */
    public function testClearObjects0()
    {
        $client = $this->getClient();

        $client->clearObjects('theIndexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/clear',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for ClearRules
     * clearRules
     */
    public function testClearRules0()
    {
        $client = $this->getClient();

        $client->clearRules('indexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/clear',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for Del
     * allow del method for a custom path with minimal parameters
     */
    public function testDel0()
    {
        $client = $this->getClient();

        $client->del('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for Del
     * allow del method for a custom path with all parameters
     */
    public function testDel1()
    {
        $client = $this->getClient();

        $client->del(
            '/test/all',
            ['query' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'DELETE',
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for DeleteApiKey
     * deleteApiKey
     */
    public function testDeleteApiKey0()
    {
        $client = $this->getClient();

        $client->deleteApiKey('myTestApiKey');

        $this->assertRequests([
            [
                'path' => '/1/keys/myTestApiKey',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for DeleteBy
     * deleteBy
     */
    public function testDeleteBy0()
    {
        $client = $this->getClient();

        $client->deleteBy(
            'theIndexName',
            ['query' => 'testQuery']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/deleteByQuery',
                'method' => 'POST',
                'body' => json_decode("{\"query\":\"testQuery\"}"),
            ],
        ]);
    }

    /**
     * Test case for DeleteIndex
     * deleteIndex
     */
    public function testDeleteIndex0()
    {
        $client = $this->getClient();

        $client->deleteIndex('theIndexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for DeleteObject
     * deleteObject
     */
    public function testDeleteObject0()
    {
        $client = $this->getClient();

        $client->deleteObject(
            'theIndexName',
            'uniqueID'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/uniqueID',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for DeleteRule
     * deleteRule
     */
    public function testDeleteRule0()
    {
        $client = $this->getClient();

        $client->deleteRule(
            'indexName',
            'id1'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/id1',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for DeleteSource
     * deleteSource
     */
    public function testDeleteSource0()
    {
        $client = $this->getClient();

        $client->deleteSource('theSource');

        $this->assertRequests([
            [
                'path' => '/1/security/sources/theSource',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for DeleteSynonym
     * deleteSynonym
     */
    public function testDeleteSynonym0()
    {
        $client = $this->getClient();

        $client->deleteSynonym(
            'indexName',
            'id1'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/id1',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for Get
     * allow get method for a custom path with minimal parameters
     */
    public function testGet0()
    {
        $client = $this->getClient();

        $client->get('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for Get
     * allow get method for a custom path with all parameters
     */
    public function testGet1()
    {
        $client = $this->getClient();

        $client->get(
            '/test/all',
            ['query' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'GET',
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for GetApiKey
     * getApiKey
     */
    public function testGetApiKey0()
    {
        $client = $this->getClient();

        $client->getApiKey('myTestApiKey');

        $this->assertRequests([
            [
                'path' => '/1/keys/myTestApiKey',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetDictionaryLanguages
     * get getDictionaryLanguages
     */
    public function testGetDictionaryLanguages0()
    {
        $client = $this->getClient();

        $client->getDictionaryLanguages();

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/*/languages',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetDictionarySettings
     * get getDictionarySettings results
     */
    public function testGetDictionarySettings0()
    {
        $client = $this->getClient();

        $client->getDictionarySettings();

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/*/settings',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetLogs
     * getLogs
     */
    public function testGetLogs0()
    {
        $client = $this->getClient();

        $client->getLogs(
            5,
            10,
            'theIndexName',
            'all'
        );

        $this->assertRequests([
            [
                'path' => '/1/logs',
                'method' => 'GET',
                'searchParams' => json_decode(
                    "{\"offset\":\"5\",\"length\":\"10\",\"indexName\":\"theIndexName\",\"type\":\"all\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for GetObject
     * getObject
     */
    public function testGetObject0()
    {
        $client = $this->getClient();

        $client->getObject(
            'theIndexName',
            'uniqueID',
            ['attr1', 'attr2']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/uniqueID',
                'method' => 'GET',
                'searchParams' => json_decode(
                    "{\"attributesToRetrieve\":\"attr1,attr2\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for GetObjects
     * getObjects
     */
    public function testGetObjects0()
    {
        $client = $this->getClient();

        $client->getObjects([
            'requests' => [
                [
                    'attributesToRetrieve' => ['attr1', 'attr2'],

                    'objectID' => 'uniqueID',

                    'indexName' => 'theIndexName',
                ],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/objects',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"requests\":[{\"attributesToRetrieve\":[\"attr1\",\"attr2\"],\"objectID\":\"uniqueID\",\"indexName\":\"theIndexName\"}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for GetRule
     * getRule
     */
    public function testGetRule0()
    {
        $client = $this->getClient();

        $client->getRule(
            'indexName',
            'id1'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/id1',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetSettings
     * getSettings
     */
    public function testGetSettings0()
    {
        $client = $this->getClient();

        $client->getSettings('theIndexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/settings',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetSources
     * getSources
     */
    public function testGetSources0()
    {
        $client = $this->getClient();

        $client->getSources();

        $this->assertRequests([
            [
                'path' => '/1/security/sources',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetSynonym
     * getSynonym
     */
    public function testGetSynonym0()
    {
        $client = $this->getClient();

        $client->getSynonym(
            'indexName',
            'id1'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/id1',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetTask
     * getTask
     */
    public function testGetTask0()
    {
        $client = $this->getClient();

        $client->getTask(
            'theIndexName',
            123
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/task/123',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetTopUserIds
     * getTopUserIds
     */
    public function testGetTopUserIds0()
    {
        $client = $this->getClient();

        $client->getTopUserIds();

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/top',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for GetUserId
     * getUserId
     */
    public function testGetUserId0()
    {
        $client = $this->getClient();

        $client->getUserId('uniqueID');

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/uniqueID',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for HasPendingMappings
     * hasPendingMappings
     */
    public function testHasPendingMappings0()
    {
        $client = $this->getClient();

        $client->hasPendingMappings(true);

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/pending',
                'method' => 'GET',
                'searchParams' => json_decode("{\"getClusters\":\"true\"}"),
            ],
        ]);
    }

    /**
     * Test case for ListApiKeys
     * listApiKeys
     */
    public function testListApiKeys0()
    {
        $client = $this->getClient();

        $client->listApiKeys();

        $this->assertRequests([
            [
                'path' => '/1/keys',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for ListClusters
     * listClusters
     */
    public function testListClusters0()
    {
        $client = $this->getClient();

        $client->listClusters();

        $this->assertRequests([
            [
                'path' => '/1/clusters',
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Test case for ListIndices
     * listIndices
     */
    public function testListIndices0()
    {
        $client = $this->getClient();

        $client->listIndices(8);

        $this->assertRequests([
            [
                'path' => '/1/indexes',
                'method' => 'GET',
                'searchParams' => json_decode("{\"page\":\"8\"}"),
            ],
        ]);
    }

    /**
     * Test case for ListUserIds
     * listUserIds
     */
    public function testListUserIds0()
    {
        $client = $this->getClient();

        $client->listUserIds(
            8,
            100
        );

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping',
                'method' => 'GET',
                'searchParams' => json_decode(
                    "{\"page\":\"8\",\"hitsPerPage\":\"100\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for MultipleBatch
     * multipleBatch
     */
    public function testMultipleBatch0()
    {
        $client = $this->getClient();

        $client->multipleBatch([
            'requests' => [
                [
                    'action' => 'addObject',

                    'body' => ['key' => 'value'],
                    'indexName' => 'theIndexName',
                ],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"requests\":[{\"action\":\"addObject\",\"body\":{\"key\":\"value\"},\"indexName\":\"theIndexName\"}]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for MultipleQueries
     * multipleQueries
     */
    public function testMultipleQueries0()
    {
        $client = $this->getClient();

        $client->multipleQueries([
            'requests' => [
                [
                    'indexName' => 'theIndexName',

                    'query' => 'test',

                    'type' => 'facet',

                    'facet' => 'theFacet',

                    'params' => 'testParam',
                ],
            ],

            'strategy' => 'stopIfEnoughMatches',
        ]);

        $this->assertRequests([
            [
                'path' => '/1/indexes/*/queries',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"requests\":[{\"indexName\":\"theIndexName\",\"query\":\"test\",\"type\":\"facet\",\"facet\":\"theFacet\",\"params\":\"testParam\"}],\"strategy\":\"stopIfEnoughMatches\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for OperationIndex
     * operationIndex
     */
    public function testOperationIndex0()
    {
        $client = $this->getClient();

        $client->operationIndex(
            'theIndexName',
            [
                'operation' => 'copy',

                'destination' => 'dest',

                'scope' => ['rules', 'settings'],
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/operation',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"operation\":\"copy\",\"destination\":\"dest\",\"scope\":[\"rules\",\"settings\"]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for PartialUpdateObject
     * partialUpdateObject
     */
    public function testPartialUpdateObject0()
    {
        $client = $this->getClient();

        $client->partialUpdateObject(
            'theIndexName',
            'uniqueID',
            [
                [
                    'id1' => 'test',

                    'id2' => ['_operation' => 'AddUnique', 'value' => 'test2'],
                ],
            ],
            true
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/uniqueID/partial',
                'method' => 'POST',
                'body' => json_decode(
                    "[{\"id1\":\"test\",\"id2\":{\"_operation\":\"AddUnique\",\"value\":\"test2\"}}]"
                ),
                'searchParams' => json_decode(
                    "{\"createIfNotExists\":\"true\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Post
     * allow post method for a custom path with minimal parameters
     */
    public function testPost0()
    {
        $client = $this->getClient();

        $client->post('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for Post
     * allow post method for a custom path with all parameters
     */
    public function testPost1()
    {
        $client = $this->getClient();

        $client->post(
            '/test/all',
            ['query' => 'parameters'],
            ['body' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'POST',
                'body' => json_decode("{\"body\":\"parameters\"}"),
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for Put
     * allow put method for a custom path with minimal parameters
     */
    public function testPut0()
    {
        $client = $this->getClient();

        $client->put('/test/minimal');

        $this->assertRequests([
            [
                'path' => '/1/test/minimal',
                'method' => 'PUT',
            ],
        ]);
    }

    /**
     * Test case for Put
     * allow put method for a custom path with all parameters
     */
    public function testPut1()
    {
        $client = $this->getClient();

        $client->put(
            '/test/all',
            ['query' => 'parameters'],
            ['body' => 'parameters']
        );

        $this->assertRequests([
            [
                'path' => '/1/test/all',
                'method' => 'PUT',
                'body' => json_decode("{\"body\":\"parameters\"}"),
                'searchParams' => json_decode("{\"query\":\"parameters\"}"),
            ],
        ]);
    }

    /**
     * Test case for RemoveUserId
     * removeUserId
     */
    public function testRemoveUserId0()
    {
        $client = $this->getClient();

        $client->removeUserId('uniqueID');

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/uniqueID',
                'method' => 'DELETE',
            ],
        ]);
    }

    /**
     * Test case for ReplaceSources
     * replaceSources
     */
    public function testReplaceSources0()
    {
        $client = $this->getClient();

        $client->replaceSources([
            ['source' => 'theSource', 'description' => 'theDescription'],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/security/sources',
                'method' => 'PUT',
                'body' => json_decode(
                    "[{\"source\":\"theSource\",\"description\":\"theDescription\"}]"
                ),
            ],
        ]);
    }

    /**
     * Test case for RestoreApiKey
     * restoreApiKey
     */
    public function testRestoreApiKey0()
    {
        $client = $this->getClient();

        $client->restoreApiKey('myApiKey');

        $this->assertRequests([
            [
                'path' => '/1/keys/myApiKey/restore',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for SaveObject
     * saveObject
     */
    public function testSaveObject0()
    {
        $client = $this->getClient();

        $client->saveObject(
            'theIndexName',
            ['objectID' => 'id', 'test' => 'val']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName',
                'method' => 'POST',
                'body' => json_decode("{\"objectID\":\"id\",\"test\":\"val\"}"),
            ],
        ]);
    }

    /**
     * Test case for SaveRule
     * saveRule
     */
    public function testSaveRule0()
    {
        $client = $this->getClient();

        $client->saveRule(
            'indexName',
            'id1',
            [
                'objectID' => 'id1',

                'conditions' => [
                    ['pattern' => 'apple', 'anchoring' => 'contains'],
                ],

                'consequence' => ['params' => ['filters' => 'brand:apple']],
            ],
            true
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/id1',
                'method' => 'PUT',
                'body' => json_decode(
                    "{\"objectID\":\"id1\",\"conditions\":[{\"pattern\":\"apple\",\"anchoring\":\"contains\"}],\"consequence\":{\"params\":{\"filters\":\"brand:apple\"}}}"
                ),
                'searchParams' => json_decode(
                    "{\"forwardToReplicas\":\"true\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SaveSynonym
     * saveSynonym
     */
    public function testSaveSynonym0()
    {
        $client = $this->getClient();

        $client->saveSynonym(
            'indexName',
            'id1',
            [
                'objectID' => 'id1',

                'type' => 'synonym',

                'synonyms' => ['car', 'vehicule', 'auto'],
            ],
            true
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/id1',
                'method' => 'PUT',
                'body' => json_decode(
                    "{\"objectID\":\"id1\",\"type\":\"synonym\",\"synonyms\":[\"car\",\"vehicule\",\"auto\"]}"
                ),
                'searchParams' => json_decode(
                    "{\"forwardToReplicas\":\"true\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SaveSynonyms
     * saveSynonyms
     */
    public function testSaveSynonyms0()
    {
        $client = $this->getClient();

        $client->saveSynonyms(
            'indexName',
            [
                [
                    'objectID' => 'id1',

                    'type' => 'synonym',

                    'synonyms' => ['car', 'vehicule', 'auto'],
                ],

                [
                    'objectID' => 'id2',

                    'type' => 'onewaysynonym',

                    'input' => 'iphone',

                    'synonyms' => ['ephone', 'aphone', 'yphone'],
                ],
            ],
            true,
            false
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/batch',
                'method' => 'POST',
                'body' => json_decode(
                    "[{\"objectID\":\"id1\",\"type\":\"synonym\",\"synonyms\":[\"car\",\"vehicule\",\"auto\"]},{\"objectID\":\"id2\",\"type\":\"onewaysynonym\",\"input\":\"iphone\",\"synonyms\":[\"ephone\",\"aphone\",\"yphone\"]}]"
                ),
                'searchParams' => json_decode(
                    "{\"forwardToReplicas\":\"true\",\"replaceExistingSynonyms\":\"false\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for Search
     * search with minimal parameters
     */
    public function testSearch0()
    {
        $client = $this->getClient();

        $client->search(
            'indexName',
            ['query' => 'myQuery']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/query',
                'method' => 'POST',
                'body' => json_decode("{\"query\":\"myQuery\"}"),
            ],
        ]);
    }

    /**
     * Test case for Search
     * search with facetFilters
     */
    public function testSearch1()
    {
        $client = $this->getClient();

        $client->search(
            'indexName',
            ['query' => 'myQuery', 'facetFilters' => ['tags:algolia']]
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/query',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"query\":\"myQuery\",\"facetFilters\":[\"tags:algolia\"]}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SearchDictionaryEntries
     * get searchDictionaryEntries results with minimal parameters
     */
    public function testSearchDictionaryEntries0()
    {
        $client = $this->getClient();

        $client->searchDictionaryEntries(
            'compounds',
            ['query' => 'foo']
        );

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/compounds/search',
                'method' => 'POST',
                'body' => json_decode("{\"query\":\"foo\"}"),
            ],
        ]);
    }

    /**
     * Test case for SearchDictionaryEntries
     * get searchDictionaryEntries results with all parameters
     */
    public function testSearchDictionaryEntries1()
    {
        $client = $this->getClient();

        $client->searchDictionaryEntries(
            'compounds',
            [
                'query' => 'foo',

                'page' => 4,

                'hitsPerPage' => 2,

                'language' => 'fr',
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/compounds/search',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"query\":\"foo\",\"page\":4,\"hitsPerPage\":2,\"language\":\"fr\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SearchForFacetValues
     * get searchForFacetValues results with minimal parameters
     */
    public function testSearchForFacetValues0()
    {
        $client = $this->getClient();

        $client->searchForFacetValues(
            'indexName',
            'facetName'
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/facets/facetName/query',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for SearchForFacetValues
     * get searchForFacetValues results with all parameters
     */
    public function testSearchForFacetValues1()
    {
        $client = $this->getClient();

        $client->searchForFacetValues(
            'indexName',
            'facetName',
            [
                'params' => "query=foo&facetFilters=['bar']",

                'facetQuery' => 'foo',

                'maxFacetHits' => 42,
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/facets/facetName/query',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"params\":\"query=foo&facetFilters=['bar']\",\"facetQuery\":\"foo\",\"maxFacetHits\":42}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SearchRules
     * searchRules
     */
    public function testSearchRules0()
    {
        $client = $this->getClient();

        $client->searchRules(
            'indexName',
            ['query' => 'something']
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/rules/search',
                'method' => 'POST',
                'body' => json_decode("{\"query\":\"something\"}"),
            ],
        ]);
    }

    /**
     * Test case for SearchSynonyms
     * searchSynonyms
     */
    public function testSearchSynonyms0()
    {
        $client = $this->getClient();

        $client->searchSynonyms('indexName');

        $this->assertRequests([
            [
                'path' => '/1/indexes/indexName/synonyms/search',
                'method' => 'POST',
            ],
        ]);
    }

    /**
     * Test case for SearchUserIds
     * searchUserIds
     */
    public function testSearchUserIds0()
    {
        $client = $this->getClient();

        $client->searchUserIds([
            'query' => 'test',

            'clusterName' => 'theClusterName',

            'page' => 5,

            'hitsPerPage' => 10,
        ]);

        $this->assertRequests([
            [
                'path' => '/1/clusters/mapping/search',
                'method' => 'POST',
                'body' => json_decode(
                    "{\"query\":\"test\",\"clusterName\":\"theClusterName\",\"page\":5,\"hitsPerPage\":10}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SetDictionarySettings
     * get setDictionarySettings results with minimal parameters
     */
    public function testSetDictionarySettings0()
    {
        $client = $this->getClient();

        $client->setDictionarySettings([
            'disableStandardEntries' => [
                'plurals' => ['fr' => false, 'en' => false, 'ru' => true],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/*/settings',
                'method' => 'PUT',
                'body' => json_decode(
                    "{\"disableStandardEntries\":{\"plurals\":{\"fr\":false,\"en\":false,\"ru\":true}}}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SetDictionarySettings
     * get setDictionarySettings results with all parameters
     */
    public function testSetDictionarySettings1()
    {
        $client = $this->getClient();

        $client->setDictionarySettings([
            'disableStandardEntries' => [
                'plurals' => ['fr' => false, 'en' => false, 'ru' => true],
                'stopwords' => ['fr' => false],
                'compounds' => ['ru' => true],
            ],
        ]);

        $this->assertRequests([
            [
                'path' => '/1/dictionaries/*/settings',
                'method' => 'PUT',
                'body' => json_decode(
                    "{\"disableStandardEntries\":{\"plurals\":{\"fr\":false,\"en\":false,\"ru\":true},\"stopwords\":{\"fr\":false},\"compounds\":{\"ru\":true}}}"
                ),
            ],
        ]);
    }

    /**
     * Test case for SetSettings
     * setSettings
     */
    public function testSetSettings0()
    {
        $client = $this->getClient();

        $client->setSettings(
            'theIndexName',
            ['paginationLimitedTo' => 10],
            true
        );

        $this->assertRequests([
            [
                'path' => '/1/indexes/theIndexName/settings',
                'method' => 'PUT',
                'body' => json_decode("{\"paginationLimitedTo\":10}"),
                'searchParams' => json_decode(
                    "{\"forwardToReplicas\":\"true\"}"
                ),
            ],
        ]);
    }

    /**
     * Test case for UpdateApiKey
     * updateApiKey
     */
    public function testUpdateApiKey0()
    {
        $client = $this->getClient();

        $client->updateApiKey(
            'myApiKey',
            [
                'acl' => ['search', 'addObject'],

                'validity' => 300,

                'maxQueriesPerIPPerHour' => 100,

                'maxHitsPerQuery' => 20,
            ]
        );

        $this->assertRequests([
            [
                'path' => '/1/keys/myApiKey',
                'method' => 'PUT',
                'body' => json_decode(
                    "{\"acl\":[\"search\",\"addObject\"],\"validity\":300,\"maxQueriesPerIPPerHour\":100,\"maxHitsPerQuery\":20}"
                ),
            ],
        ]);
    }
}

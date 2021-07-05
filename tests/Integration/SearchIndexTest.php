<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Support\Helpers;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class SearchIndexTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->indexes['main'])) {
            $this->indexes['main'] = TestHelper::getTestIndexName('indexing');
        }
    }

    public function testIndexing()
    {
        $responses = [];
        /** @var SearchIndex $index */
        $index = TestHelper::getClient()->initIndex($this->indexes['main']);

        /* adding an object with object id */
        $obj1 = TestHelper::createRecord(null);
        $responses[] = $index->saveObject($obj1);

        /* adding an object w/o object id s */
        $obj2 = TestHelper::createRecord(false);
        $responses[] = $index->saveObject($obj2, ['autoGenerateObjectIDIfNotExist' => true]);

        /* saving an empty set of objects */
        $responses[] = $index->saveObjects([]);

        /* adding two objects with object id  */
        $obj3 = TestHelper::createRecord(null);
        $obj4 = TestHelper::createRecord(null);
        $responses[] = $index->saveObjects([$obj3, $obj4]);

        /* adding two objects w/o object id  */
        $obj5 = TestHelper::createRecord(false);
        $obj6 = TestHelper::createRecord(false);
        $responses[] = $index->saveObjects([$obj5, $obj6], ['autoGenerateObjectIDIfNotExist' => true]);

        /* adding 1000 objects with object id with 10 batch */
        $objects = [];
        for ($i = 1; $i <= 1000; $i++) {
            $objects[$i] = TestHelper::createRecord($i);
        }

        $objectsChunks = array_chunk($objects, 100);
        foreach ($objectsChunks as $chunk) {
            $request = Helpers::buildBatch($chunk, 'addObject');
            $responses[] = $index->batch($request);
        }

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Check 6 first records with getObject */
        $objectID1 = $responses[0][0]['objectIDs'][0];

        $objectID2 = $responses[1][0]['objectIDs'][0];

        $objectID3 = $responses[3][0]['objectIDs'][0];
        $objectID4 = $responses[3][0]['objectIDs'][1];

        $objectID5 = $responses[4][0]['objectIDs'][0];
        $objectID6 = $responses[4][0]['objectIDs'][1];

        $result1 = $index->getObject($objectID1);
        $this->assertEquals($obj1['name'], $result1['name']);

        $result2 = $index->getObject($objectID2);
        $this->assertEquals($obj2['name'], $result2['name']);

        $result3 = $index->getObject($objectID3);
        $this->assertEquals($obj3['name'], $result3['name']);
        $result4 = $index->getObject($objectID4);
        $this->assertEquals($obj4['name'], $result4['name']);

        $result5 = $index->getObject($objectID5);
        $this->assertEquals($obj5['name'], $result5['name']);
        $result6 = $index->getObject($objectID6);
        $this->assertEquals($obj6['name'], $result6['name']);

        /* Check 1000 remaining records with getObjects */
        $results = $index->getObjects(array_keys($objects));
        $this->assertEquals(array_values($objects), $results['results']);

        /*  Browse all records with browseObjects */
        $iterator = $index->browseObjects();
        $this->assertCount(1006, $iterator);

        $results = iterator_to_array($iterator);
        foreach ($objects as $object) {
            $this->assertTrue(in_array($object, $results));
        }

        /* Alter 1 record with partialUpdateObject */
        $obj1['name'] = 'This is an altered name 1';
        $responses[] = $index->partialUpdateObject($obj1);

        /* Alter 2 records with partialUpdateObjects */
        $obj3['name'] = 'This is an altered name 3';
        $obj4['name'] = 'This is an altered name 4';
        $responses[] = $index->partialUpdateObjects([$obj3, $obj4]);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Check previous altered records with getObject */
        $this->assertEquals($index->getObject($objectID1), $obj1);
        $this->assertEquals($index->getObject($objectID3), $obj3);
        $this->assertEquals($index->getObject($objectID4), $obj4);

        /* adding an object w/o object id s */
        $objWithTag = TestHelper::createRecord(null);
        $objWithTag['_tags'] = ['algolia'];
        $responses[] = $index->saveObject($objWithTag)->wait();

        /*  Delete the first record with deleteObject */
        $responses[] = $index->deleteObject($objectID1)->wait();

        /* Delete the record with the "algolia" tag */
        $responses[] = $index->deleteBy(['tagFilters' => ['algolia']])->wait();

        /* Delete the 5 remaining first records with deleteObjects */
        $objectsIDs = [$objectID1, $objectID2, $objectID3, $objectID4, $objectID5, $objectID6];
        $responses[] = $index->deleteObjects($objectsIDs)->wait();

        /* Browse all objects with browseObjects */
        $iterator = $index->browseObjects();
        $this->assertCount(1000, $iterator);

        /* Delete the 1000 remaining records with clearObjects */
        $responses[] = $index->clearObjects();

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Browse all objects with browseObjects */
        $iterator = $index->browseObjects();
        $this->assertCount(0, $iterator);
    }

    public function testSettings()
    {
        $this->indexes['settings'] = TestHelper::getTestIndexName('settings');

        /** @var SearchIndex $settingsIndex */
        $settingsIndex = TestHelper::getClient()->initIndex($this->indexes['settings']);

        $responses = [];

        /* adding an object with object id */
        $obj1 = TestHelper::createRecord(null);
        $responses[] = $settingsIndex->saveObject($obj1);

        $settings = [
            'searchableAttributes' => [
                'attribute1',
                'attribute2',
                'attribute3',
                'ordered(attribute4)',
                'unordered(attribute5)',
            ],
            'attributesForFaceting' => ['attribute1', 'filterOnly(attribute2)', 'searchable(attribute3)'],
            'unretrievableAttributes' => ['attribute1', 'attribute2'],
            'attributesToRetrieve' => ['attribute3', 'attribute4'],
            'ranking' => [
                'asc(attribute1)',
                'desc(attribute2)',
                'attribute',
                'custom',
                'exact',
                'filters',
                'geo',
                'proximity',
                'typo',
                'words',
            ],
            'customRanking' => ['asc(attribute1)', 'desc(attribute1)'],
            'replicas' => [$this->indexes['settings'].'_replica1', $this->indexes['settings'].'_replica2'],
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => 'count',
            'attributesToHighlight' => ['attribute1', 'attribute2'],
            'attributesToSnippet' => ['attribute1:10', 'attribute2:8'],
            'highlightPreTag' => '<strong>',
            'highlightPostTag' => '</strong>',
            'snippetEllipsisText' => ' and so on.',
            'restrictHighlightAndSnippetArrays' => true,
            'hitsPerPage' => 42,
            'paginationLimitedTo' => 43,
            'minWordSizefor1Typo' => 2,
            'minWordSizefor2Typos' => 6,
            'typoTolerance' => 'false',
            'allowTyposOnNumericTokens' => false,
            'ignorePlurals' => true,
            'disableTypoToleranceOnAttributes' => ['attribute1', 'attribute2'],
            'disableTypoToleranceOnWords' => ['word1', 'word2'],
            'separatorsToIndex' => '()[]',
            'queryType' => 'prefixNone',
            'removeWordsIfNoResults' => 'allOptional',
            'advancedSyntax' => true,
            'optionalWords' => ['word1', 'word2'],
            'removeStopWords' => true,
            'disablePrefixOnAttributes' => ['attribute1', 'attribute2'],
            'disableExactOnAttributes' => ['attribute1', 'attribute2'],
            'exactOnSingleWordQuery' => 'word',
            'enableRules' => false,
            'numericAttributesForFiltering' => ['attribute1', 'attribute2'],
            'allowCompressionOfIntegerArray' => true,
            'attributeForDistinct' => 'attribute1',
            'distinct' => 2,
            'replaceSynonymsInHighlight' => false,
            'minProximity' => 7,
            'responseFields' => ['hits', 'hitsPerPage'],
            'maxFacetHits' => 100,
            'camelCaseAttributes' => ['attribute1', 'attribute2'],
            'decompoundedAttributes' => ['de' => ['attribute1', 'attribute2'], 'fi' => ['attribute3']],
            'keepDiacriticsOnCharacters' => 'øé',
            'queryLanguages' => ['en', 'fr'],
            'alternativesAsExact' => ['ignorePlurals'],
            'advancedSyntaxFeatures' => ['exactPhrase'],
            'userData' => '{"customUserData": 42.0}',
            'indexLanguages' => ['ja'],
            'customNormalization' => ['default' => ['ä' => 'ae', 'ö' => 'oe']],
            'enablePersonalization' => true,
        ];

        $responses[] = $settingsIndex->setSettings($settings);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Because the response settings dict contains the extra version key, we
        # also add it to the expected settings dict to prevent the test to fail
        # for a missing key. */
        $settings['version'] = 2;

        /* Check values from getSettings method */
        $fetchedSettings = $settingsIndex->getSettings();
        $this->assertEquals($settings, $fetchedSettings);

        $settings['typoTolerance'] = 'min';
        $settings['ignorePlurals'] = ['en', 'fr'];
        $settings['removeStopWords'] = ['en', 'fr'];
        $settings['distinct'] = true;

        $responses[] = $settingsIndex->setSettings($settings)->wait();

        /* Check new values from getSettings method */
        $fetchedSettings = $settingsIndex->getSettings();
        $this->assertEquals($settings, $fetchedSettings);

        /* Ensure a setSettings call also works when `decompoundedAttributes` is an empty array */
        $settingsIndex->setSettings([
            'decompoundedAttributes' => [],
        ]);
    }

    public function testSearch()
    {
        $this->indexes['search'] = TestHelper::getTestIndexName('search');

        /** @var SearchIndex $searchIndex */
        $searchIndex = TestHelper::getClient()->initIndex($this->indexes['search']);

        $responses = [];

        $employees = [
            ['company' => 'Algolia', 'name' => 'Julien Lemoine', 'objectID' => 'julien-lemoine'],
            ['company' => 'Algolia', 'name' => 'Nicolas Dessaigne', 'objectID' => 'nicolas-dessaigne'],
            ['company' => 'Amazon', 'name' => 'Jeff Bezos'],
            ['company' => 'Apple', 'name' => 'Steve Jobs'],
            ['company' => 'Apple', 'name' => 'Steve Wozniak'],
            ['company' => 'Arista Networks', 'name' => 'Jayshree Ullal'],
            ['company' => 'Google', 'name' => 'Larry Page'],
            ['company' => 'Google', 'name' => 'Rob Pike'],
            ['company' => 'Google', 'name' => 'Serguey Brin'],
            ['company' => 'Microsoft', 'name' => 'Bill Gates'],
            ['company' => 'SpaceX', 'name' => 'Elon Musk'],
            ['company' => 'Tesla', 'name' => 'Elon Musk'],
            ['company' => 'Yahoo', 'name' => 'Marissa Mayer'],
        ];

        $responses[] = $searchIndex->saveObjects($employees, ['autoGenerateObjectIDIfNotExist' => true]);
        $responses[] = $searchIndex->setSettings(['attributesForFaceting' => ['searchable(company)']]);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $res = $searchIndex->search('algolia');

        /* Check if the number of results is 2  */
        $this->assertCount(2, $res['hits']);

        /* Check item positions */
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'nicolas-dessaigne'), 0);
        $this->assertEquals(SearchIndex::getObjectPosition($res, 'julien-lemoine'), 1);
        $this->assertEquals(SearchIndex::getObjectPosition($res, ''), -1);

        /* Check that no object is found when callback returns always false */
        try {
            $searchIndex->findObject(function () { return false; });
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        /* Check that first object is found when callback returns always true */
        $found = $searchIndex->findObject(function () { return true; });
        $this->assertEquals($found['position'], 0);
        $this->assertEquals($found['page'], 0);

        /* Callback that checks if the company is Apple */
        $callback = function ($obj) {
            return array_key_exists('company', $obj) && 'Apple' === $obj['company'];
        };

        /* Check that no "apple" employee is returned when we search for "Algolia" */
        try {
            $searchIndex->findObject($callback, ['query' => 'algolia']);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        /* Check that no object is found when we limit the search to the 5 first objects */
        try {
            $searchIndex->findObject($callback, ['query' => '', 'paginate' => false, 'hitsPerPage' => 5]);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\ObjectNotFoundException', $e);
        }

        /* Check that we actually find the object when we paginate (on page 2) */
        $obj = $searchIndex->findObject($callback, ['query' => '', 'paginate' => true, 'hitsPerPage' => 5]);
        $this->assertEquals($obj['position'], 0);
        $this->assertEquals($obj['page'], 2);

        $res = $searchIndex->search('elon', ['clickAnalytics' => true]);
        $this->assertNotEmpty($res['queryID']);

        $res = $searchIndex->search('', ['facets' => '*', 'facetFilters' => 'company:tesla']);
        $this->assertCount(1, $res['hits']);

        $res = $searchIndex->search(
            '',
            ['facets' => '*', 'filters' => 'company:tesla OR company:spacex']
        );
        $this->assertCount(2, $res['hits']);

        $res = $searchIndex->searchForFacetValues('company', 'a');
        $resultFacets = [];
        foreach ($res['facetHits'] as $facet) {
            $resultFacets[] = $facet['value'];
        }

        $this->assertContains('Algolia', $resultFacets);
        $this->assertContains('Amazon', $resultFacets);
        $this->assertContains('Apple', $resultFacets);
        $this->assertContains('Arista Networks', $resultFacets);
    }

    public function testSynonyms()
    {
        $this->indexes['synonyms'] = TestHelper::getTestIndexName('synonyms');

        /** @var SearchIndex $synonymsIndex */
        $synonymsIndex = TestHelper::getClient()->initIndex($this->indexes['synonyms']);

        $responses = [];

        $consoles = [
            ['console' => 'Sony PlayStation <PLAYSTATIONVERSION>'],
            ['console' => 'Nintendo Switch'],
            ['console' => 'Nintendo Wii U'],
            ['console' => 'Nintendo Game Boy Advance'],
            ['console' => 'Microsoft Xbox'],
            ['console' => 'Microsoft Xbox 360'],
            ['console' => 'Microsoft Xbox One'],
        ];

        $responses[] = $synonymsIndex->saveObjects($consoles, ['autoGenerateObjectIDIfNotExist' => true]);

        $nWaySynonym = [
            'objectID' => 'gba',
            'type' => 'synonym',
            'synonyms' => ['gameboy advance', 'game boy advance'],
        ];

        $syn1 = [
            'objectID' => 'wii_to_wii_u',
            'type' => 'onewaysynonym',
            'input' => 'wii',
            'synonyms' => ['wii U'],
        ];

        $syn2 = [
            'objectID' => 'playstation_version_placeholder',
            'type' => 'placeholder',
            'placeholder' => '<PLAYSTATIONVERSION>',
            'replacements' => ['1', 'One', '2', '3', '4', '4 Pro'],
        ];

        $syn3 = [
            'objectID' => 'ps4',
            'type' => 'altcorrection1',
            'word' => 'ps4',
            'corrections' => ['playstation4'],
        ];

        $syn4 = [
            'objectID' => 'psone',
            'type' => 'altcorrection2',
            'word' => 'psone',
            'corrections' => ['playstationone'],
        ];

        $synonyms = [$syn1, $syn2, $syn3, $syn4];

        $responses[] = $synonymsIndex->saveSynonym($nWaySynonym);
        $responses[] = $synonymsIndex->saveSynonyms($synonyms);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $this->assertEquals($nWaySynonym, $synonymsIndex->getSynonym('gba'));
        $this->assertEquals($synonyms[0], $synonymsIndex->getSynonym('wii_to_wii_u'));
        $this->assertEquals($synonyms[1], $synonymsIndex->getSynonym('playstation_version_placeholder'));
        $this->assertEquals($synonyms[2], $synonymsIndex->getSynonym('ps4'));
        $this->assertEquals($synonyms[3], $synonymsIndex->getSynonym('psone'));

        $res = $synonymsIndex->searchSynonyms('');
        $this->assertEquals(5, $res['nbHits']);

        $iterator = $synonymsIndex->browseSynonyms(['hitsPerPage' => 1]);
        $synonymsToCheck = [$nWaySynonym, $syn1, $syn2, $syn3, $syn4];
        foreach ($iterator as $synonym) {
            $this->assertTrue(in_array($synonym, $synonymsToCheck));
        }

        $synonymsIndex->deleteSynonym('gba')->wait();

        try {
            $synonymsIndex->getSynonym('gba');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $synonymsIndex->clearSynonyms()->wait();

        $res = $synonymsIndex->searchSynonyms('');
        $this->assertEquals($res['nbHits'], 0);
    }

    public function testQueryRules()
    {
        $this->indexes['rules'] = TestHelper::getTestIndexName('rules');

        /** @var SearchIndex $rulesIndex */
        $rulesIndex = TestHelper::getClient()->initIndex($this->indexes['rules']);

        $responses = [];

        $smartphones = [
            ['objectID' => 'iphone_7', 'brand' => 'Apple', 'model' => '7'],
            ['objectID' => 'iphone_8', 'brand' => 'Apple', 'model' => '7'],
            ['objectID' => 'iphone_x', 'brand' => 'Apple', 'model' => '7'],
            ['objectID' => 'one_plus_one', 'brand' => 'OnePlus', 'model' => 'One'],
            ['objectID' => 'one_plus_two', 'brand' => 'OnePlus', 'model' => 'Two'],
        ];

        $responses[] = $rulesIndex->saveObjects($smartphones, ['autoGenerateObjectIDIfNotExist' => true]);
        $responses[] = $rulesIndex->setSettings(['attributesForFaceting' => ['brand', 'model']]);

        $rule1 = [
            'objectID' => 'brand_automatic_faceting',
            'enabled' => false,
            'condition' => [
                'anchoring' => 'is',
                'pattern' => '{facet:brand}',
            ],
            'consequence' => [
                'params' => [
                    'automaticFacetFilters' => [
                        [
                            'facet' => 'brand',
                            'disjunctive' => true,
                            'score' => 42,
                        ],
                    ],
                ],
            ],
            'validity' => [
                [
                    'from' => 1532439300, // 07/24/2018 13:35:00 UTC
                    'until' => 1532525700, // 07/25/2018 13:35:00 UTC
                ],
                [
                    'from' => 1532612100, // 07/26/2018 13:35:00 UTC
                    'until' => 1532698500, // 07/27/2018 13:35:00 UTC
                ],
            ],
            'description' => 'Automatic apply the faceting on `brand` if a brand value is found in the query',
        ];

        $responses[] = $rulesIndex->saveRule($rule1);

        $rule2 = [
            'objectID' => 'query_edits',
            'conditions' => [
                [
                    'anchoring' => 'is',
                    'pattern' => 'mobile phone',
                    'alternatives' => true,
                ],
            ],
            'consequence' => [
                'filterPromotes' => false,
                'params' => [
                    'query' => [
                        'edits' => [
                            [
                                'type' => 'remove',
                                'delete' => 'mobile',
                            ],
                            [
                                'type' => 'replace',
                                'delete' => 'phone',
                                'insert' => 'iphone',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $rule3 = [
            'objectID' => 'query_promo',
            'consequence' => [
                'params' => [
                    'filters' => 'brand:OnePlus',
                ],
            ],
        ];

        $rule4 = [
            'objectID' => 'query_promo_summer',
            'conditions' => [
                [
                    'context' => 'summer',
                ],
            ],
            'consequence' => [
                'params' => [
                    'filters' => 'model:One',
                ],
            ],
        ];

        $additionalRules = [$rule2, $rule3, $rule4];
        $responses[] = $rulesIndex->saveRules($additionalRules);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $res = $rulesIndex->search('', ['ruleContexts' => 'summer']);
        $this->assertCount(1, $res['hits']);

        $res = $rulesIndex->getRule($rule1['objectID']);
        $this->assertEquals(TestHelper::formatRule($res), $rule1);

        $res = $rulesIndex->getRule($rule2['objectID']);
        $this->assertEquals(TestHelper::formatRule($res), $rule2);

        $res = $rulesIndex->getRule($rule3['objectID']);
        $this->assertEquals(TestHelper::formatRule($res), $rule3);

        $res = $rulesIndex->getRule($rule4['objectID']);
        $this->assertEquals(TestHelper::formatRule($res), $rule4);

        $allRules = [$rule1, $rule2, $rule3, $rule4];

        $res = $rulesIndex->searchRules('');
        foreach ($res['hits'] as $fetchedRule) {
            $this->assertTrue(in_array(TestHelper::formatRule($fetchedRule), $allRules));
        }

        $iterator = $rulesIndex->browseRules(['hitsPerPage' => 1]);
        foreach ($iterator as $fetchedRule) {
            $this->assertTrue(in_array(TestHelper::formatRule($fetchedRule), $allRules));
        }

        $rulesIndex->deleteRule($rule1['objectID'])->wait();

        try {
            $rulesIndex->getRule($rule1['objectID']);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $rulesIndex->clearRules()->wait();
        $res = $rulesIndex->searchRules('');
        $this->assertCount(0, $res['hits']);

        $ruleString = '{
          "objectID": "query_edits",
          "condition": {"anchoring": "is", "pattern": "mobile phone"},
          "consequence": {
            "params": {
              "query": {
               "remove": ["mobile", "phone"]
              }
            }
          }
        }';

        $serializedRule = json_decode($ruleString, true);
        $rulesIndex->saveRule($serializedRule)->wait();

        $res = $rulesIndex->getRule($serializedRule['objectID']);
        $this->assertEquals(TestHelper::formatRule($res), $serializedRule);
    }

    public function testBatching()
    {
        $this->indexes['index_batching'] = TestHelper::getTestIndexName('index_batching');

        /** @var SearchIndex $batchingIndex */
        $batchingIndex = TestHelper::getClient()->initIndex($this->indexes['index_batching']);

        $figures = [
            ['objectID' => 'one', 'key' => 'value'],
            ['objectID' => 'two', 'key' => 'value'],
            ['objectID' => 'three', 'key' => 'value'],
            ['objectID' => 'four', 'key' => 'value'],
            ['objectID' => 'five', 'key' => 'value'],
        ];

        $batchingIndex->saveObjects($figures, ['autoGenerateObjectIDIfNotExist' => true])->wait();

        $batch = [
            ['action' => 'addObject', 'body' => ['objectID' => 'zero', 'key' => 'value']],
            ['action' => 'updateObject', 'body' => ['objectID' => 'one', 'k' => 'v']],
            ['action' => 'partialUpdateObject', 'body' => ['objectID' => 'two', 'k' => 'v']],
            ['action' => 'partialUpdateObject', 'body' => ['objectID' => 'two_bis', 'key' => 'value']],
            ['action' => 'partialUpdateObjectNoCreate', 'body' => ['objectID' => 'three', 'k' => 'v']],
            ['action' => 'deleteObject', 'body' => ['objectID' => 'four']],
        ];

        $batchingIndex->batch($batch)->wait();

        $figuresAfterBatch = [
            ['objectID' => 'zero', 'key' => 'value'],
            ['objectID' => 'one', 'k' => 'v'],
            ['objectID' => 'two', 'key' => 'value', 'k' => 'v'],
            ['objectID' => 'two_bis', 'key' => 'value'],
            ['objectID' => 'three', 'key' => 'value', 'k' => 'v'],
            ['objectID' => 'five', 'key' => 'value'],
        ];

        $iterator = $batchingIndex->browseObjects();
        $fetchedResults = [];
        foreach ($iterator as $object) {
            $fetchedResults[] = $object;
            $this->assertTrue(in_array($object, $figuresAfterBatch));
        }

        $this->assertCount(count($fetchedResults), $figuresAfterBatch);
    }

    public function testReplacing()
    {
        $this->indexes['replacing'] = TestHelper::getTestIndexName('replacing');

        /** @var SearchIndex $replacingIndex */
        $replacingIndex = TestHelper::getClient()->initIndex($this->indexes['replacing']);

        $responses = [];

        $responses[] = $replacingIndex->saveObject(['objectID' => 'one']);

        $rule = [
            'objectID' => 'one',
            'condition' => [
                'anchoring' => 'is',
                'pattern' => 'pattern',
            ],
            'consequence' => [
                'params' => [
                    'query' => [
                        'edits' => [
                            [
                                'type' => 'remove',
                                'delete' => 'pattern',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses[] = $replacingIndex->saveRule($rule);

        $synonym = [
            'objectID' => 'one',
            'type' => 'synonym',
            'synonyms' => ['one', 'two'],
        ];

        $responses[] = $replacingIndex->saveSynonym($synonym);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        $responses[] = $replacingIndex->replaceAllObjects([['objectID' => 'two']]);

        $altRule = $rule;
        $altRule['objectID'] = 'two';
        $responses[] = $replacingIndex->replaceAllRules([$altRule]);

        $altSynonym = $synonym;
        $altSynonym['objectID'] = 'two';
        $responses[] = $replacingIndex->replaceAllSynonyms([$altSynonym]);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Check Object replacement */
        try {
            $replacingIndex->getObject('one');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $res = $replacingIndex->getObject('two');
        $this->assertEquals('two', $res['objectID']);

        /* Check Rule replacement */
        try {
            $replacingIndex->getRule('one');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $res = $replacingIndex->getRule('two');
        $this->assertEquals('two', $res['objectID']);

        /* Check Synonym replacement */
        try {
            $replacingIndex->getSynonym('one');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Algolia\AlgoliaSearch\Exceptions\NotFoundException', $e);
        }

        $res = $replacingIndex->getSynonym('two');
        $this->assertEquals('two', $res['objectID']);
    }

    public function testIndexExists()
    {
        $this->indexes['exists'] = TestHelper::getTestIndexName('exists');

        /** @var SearchIndex $existsIndex */
        $existsIndex = TestHelper::getClient()->initIndex($this->indexes['exists']);

        $this->assertFalse($existsIndex->exists());

        /* adding a object w/o object id s */
        $obj = TestHelper::createRecord();
        $existsIndex->saveObject($obj, ['autoGenerateObjectIDIfNotExist' => true])->wait();

        $this->assertTrue($existsIndex->exists());

        $existsIndex->delete()->wait();

        $this->assertFalse($existsIndex->exists());
    }
}

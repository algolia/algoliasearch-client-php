<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AccountClient;
use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class AccountTest extends BaseTest
{
    protected $secondaryIndexes = [];

    public function testCopyIndex()
    {
        $this->indexes['copy_index'] = TestHelper::getTestIndexName('copy_index');
        $this->indexes['copy_index_2'] = TestHelper::getTestIndexName('copy_index_2');

        /** @var SearchIndex $copyIndex */
        $copyIndex = TestHelper::getClient()->initIndex($this->indexes['copy_index']);
        $secondaryIndex = TestHelper::getClient()->initIndex($this->indexes['copy_index_2']);

        try {
            AccountClient::copyIndex($copyIndex, $secondaryIndex)->wait();
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e);
            $this->assertEquals(
                'If both index are on the same app, please use SearchClient::copyIndex method instead.',
                $e->getMessage()
            );
        }

        $secondaryConfig = [
            'appId' => getenv('ALGOLIA_APPLICATION_ID_2'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_2'),
        ];

        $secondaryClient = TestHelper::getClient($secondaryConfig);
        $this->secondaryIndexes['copy_index_2'] = TestHelper::getTestIndexName('copy_index_2');

        /** @var SearchIndex $secondaryIndex */
        $secondaryIndex = $secondaryClient->initIndex($this->secondaryIndexes['copy_index_2']);

        $responses = [];
        $responses[] = $copyIndex->saveObject(
            ['objectID' => 'one'],
            ['autoGenerateObjectIDIfNotExist' => true]
        );

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

        $responses[] = $copyIndex->saveRule($rule);

        $synonym = [
            'objectID' => 'one',
            'type' => 'synonym',
            'synonyms' => ['one', 'two'],
        ];

        $responses[] = $copyIndex->saveSynonym($synonym);

        $responses[] = $copyIndex->setSettings(['searchableAttributes' => ['objectID']]);

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        AccountClient::copyIndex($copyIndex, $secondaryIndex)->wait();

        $result = $secondaryIndex->getObject('one');
        $this->assertEquals('one', $result['objectID']);

        $result = $secondaryIndex->getSettings();
        $this->assertEquals(['objectID'], $result['searchableAttributes']);

        $result = $secondaryIndex->getRule($rule['objectID']);
        $this->assertEquals('one', $result['objectID']);

        $result = $secondaryIndex->getSynonym($synonym['objectID']);
        $this->assertEquals('one', $result['objectID']);

        try {
            AccountClient::copyIndex($copyIndex, $secondaryIndex)->wait();
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e);
            $this->assertEquals(
                'Destination index already exists. Please delete it before copying index across applications.',
                $e->getMessage()
            );
        }
    }
}

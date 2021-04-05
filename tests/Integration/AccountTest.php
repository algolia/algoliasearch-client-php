<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AccountClient;
use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class AccountTest extends BaseTest
{
    protected $secondaryIndexes = array();

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

        $secondaryConfig = array(
            'appId' => getenv('ALGOLIA_APPLICATION_ID_2'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_2'),
        );

        $secondaryClient = TestHelper::getClient($secondaryConfig);
        $this->secondaryIndexes['copy_index_2'] = TestHelper::getTestIndexName('copy_index_2');

        /** @var SearchIndex $secondaryIndex */
        $secondaryIndex = $secondaryClient->initIndex($this->secondaryIndexes['copy_index_2']);

        $responses = array();
        $responses[] = $copyIndex->saveObject(
            array('objectID' => 'one'),
            array('autoGenerateObjectIDIfNotExist' => true)
        );

        $rule = array(
            'objectID' => 'one',
            'condition' => array(
                'anchoring' => 'is',
                'pattern' => 'pattern',
            ),
            'consequence' => array(
                'params' => array(
                    'query' => array(
                        'edits' => array(
                            array(
                                'type' => 'remove',
                                'delete' => 'pattern',
                            ),
                        ),
                    ),
                ),
            ),
        );

        $responses[] = $copyIndex->saveRule($rule);

        $synonym = array(
            'objectID' => 'one',
            'type' => 'synonym',
            'synonyms' => array('one', 'two'),
        );

        $responses[] = $copyIndex->saveSynonym($synonym);

        $responses[] = $copyIndex->setSettings(array('searchableAttributes' => array('objectID')));

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        AccountClient::copyIndex($copyIndex, $secondaryIndex)->wait();

        $result = $secondaryIndex->getObject('one');
        $this->assertEquals('one', $result['objectID']);

        $result = $secondaryIndex->getSettings();
        $this->assertEquals(array('objectID'), $result['searchableAttributes']);

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

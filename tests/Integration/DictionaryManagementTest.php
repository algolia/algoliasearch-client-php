<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\TestHelper;

class DictionaryManagementTest extends BaseTest
{
    /** @var SearchClient */
    private $client;

    public function setUp()
    {
        if (!isset($this->client)) {
            $this->client = TestHelper::getClient(array(
                'appId' => getenv('ALGOLIA_APPLICATION_ID_2'),
                'apiKey' => getenv('ALGOLIA_ADMIN_KEY_2'),
            ));
        }
    }

    public function tearDown()
    {
        $this->client->clearDictionaryEntries('stopwords')->wait();
        $this->client->clearDictionaryEntries('plurals')->wait();
        $this->client->clearDictionaryEntries('compounds')->wait();
    }

    private static function randomString()
    {
        return substr(str_shuffle(md5(microtime())), 0, 10);
    }

    /**
     * @group dictionaries
     */
    public function testStopWordDictionaryManagement()
    {
        $objectID = self::randomString();

        $searchResponse = $this->client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array('objectID' => $objectID, 'language' => 'en', 'word' => 'down');
        $this->client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $this->client->searchDictionaryEntries('stopwords', $objectID);
        $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits']);

        $this->assertCount(1, $addedEntries);

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);

        $this->client->deleteDictionaryEntries('stopwords', array($objectID))->wait();

        $searchResponse = $this->client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $oldDictionaryState = $this->client->searchDictionaryEntries('stopwords', '');
        $oldDictionaryEntries = array_map(function ($hit) {
            unset($hit['type']);

            return $hit;
        }, $oldDictionaryState['hits']);

        $this->client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $this->client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(1, $this->findEntriesWithObjectID($entry['objectID'], $searchResponse['hits']));

        $this->client->replaceDictionaryEntries('stopwords', $oldDictionaryEntries)->wait();

        $searchResponse = $this->client->searchDictionaryEntries('stopwords', '');
        $this->assertCount(0, $this->findEntriesWithObjectID($entry['objectID'], $searchResponse['hits']));

        $stopwordSettings = array(
            'disableStandardEntries' => array(
                'stopwords' => array(
                    'en' => true,
                ),
            ),
        );

        $this->client->setDictionarySettings($stopwordSettings)->wait();

        $this->assertEquals($stopwordSettings, $this->client->getDictionarySettings());
    }

    /**
     * @group dictionaries
     */
    public function testCompoundDictionaryManagement()
    {
        $objectID = self::randomString();

        $searchResponse = $this->client->searchDictionaryEntries('compounds', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array(
            'objectID' => $objectID,
            'language' => 'de',
            'word' => 'kopfschmerztablette',
            'decomposition' => array(
                'kopf', 'schmerz', 'tablette',
            ),
        );

        $this->client->saveDictionaryEntries(
            'compounds',
            array($entry)
        )->wait();

        $searchResponse = $this->client->searchDictionaryEntries('compounds', $objectID);
        $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits']);

        $this->assertCount(1, $addedEntries);

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);
        $this->assertEquals($entry['decomposition'], $savedEntry['decomposition']);

        $this->client->deleteDictionaryEntries('compounds', array($objectID))->wait();

        $searchResponse = $this->client->searchDictionaryEntries('compounds', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));
    }

    /**
     * @group dictionaries
     */
    public function testPluralsDictionaryManagement()
    {
        $objectID = self::randomString();

        $searchResponse = $this->client->searchDictionaryEntries('plurals', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array(
            'objectID' => $objectID,
            'language' => 'fr',
            'words' => array(
                'cheval', 'chevaux',
            ),
        );

        $this->client->saveDictionaryEntries(
            'plurals',
            array($entry)
        )->wait();

        $searchResponse = $this->client->searchDictionaryEntries('plurals', $objectID);
        $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits']);

        $this->assertCount(1, $addedEntries);

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['words'], $savedEntry['words']);

        $this->client->deleteDictionaryEntries('plurals', array($objectID))->wait();

        $searchResponse = $this->client->searchDictionaryEntries('plurals', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));
    }

    private function findEntriesWithObjectID($objectID, $entries)
    {
        return array_values(
            array_filter(
                $entries,
                function ($hit) use ($objectID) {
                    return $hit['objectID'] === $objectID;
                }
            )
        );
    }
}

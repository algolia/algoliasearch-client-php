<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class DictionaryManagementTest extends AlgoliaIntegrationTestCase
{
    private static $dictionaryClient;

    private static function getDictionaryClient()
    {
        if (!isset(self::$dictionaryClient)) {
            self::$dictionaryClient = static::newClient(array(
                'appId' => getenv('ALGOLIA_APPLICATION_ID_2'),
                'apiKey' => getenv('ALGOLIA_ADMIN_KEY_2'),
            ));
        }

        return self::$dictionaryClient;
    }

    private static function randomString()
    {
        return substr(str_shuffle(md5(microtime())), 0, 10);
    }

    public function testStopWordDictionaryManagement()
    {
        $client = self::getDictionaryClient();
        $objectID = self::randomString();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array('objectID' => $objectID, 'language' => 'en', 'word' => 'down');
        $client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $objectID);

        $this->assertCount(
            1,
            $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits'])
        );

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);

        $client->deleteDictionaryEntries('stopwords', array($objectID))->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $oldDictionaryState = $client->searchDictionaryEntries('stopwords', '');
        $oldDictionaryEntries = array_map(function ($hit) {
            unset($hit['type']);

            return $hit;
        }, $oldDictionaryState['hits']);

        $client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $objectID);
        $this->assertCount(1, $this->findEntriesWithObjectID($entry['objectID'], $searchResponse['hits']));

        $client->replaceDictionaryEntries('stopwords', $oldDictionaryEntries)->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', '');
        $this->assertCount(0, $this->findEntriesWithObjectID($entry['objectID'], $searchResponse['hits']));

        $stopwordSettings = array(
            'disableStandardEntries' => array(
                'stopwords' => array(
                    'en' => true,
                ),
            ),
        );

        $client->setDictionarySettings($stopwordSettings)->wait();

        $this->assertEquals($stopwordSettings, $client->getDictionarySettings());
    }

    public function testCompoundDictionaryManagement()
    {
        $client = self::getDictionaryClient();
        $objectID = self::randomString();

        $searchResponse = $client->searchDictionaryEntries('compounds', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array(
            'objectID' => $objectID,
            'language' => 'de',
            'word' => 'kopfschmerztablette',
            'decomposition' => array(
                'kopf', 'schmerz', 'tablette',
            ),
        );

        $client->saveDictionaryEntries(
            'compounds',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('compounds', $objectID);

        $this->assertCount(
            1,
            $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits'])
        );

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);
        $this->assertEquals($entry['decomposition'], $savedEntry['decomposition']);

        $client->deleteDictionaryEntries('compounds', array($objectID))->wait();

        $searchResponse = $client->searchDictionaryEntries('compounds', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));
    }

    public function testPluralsDictionaryManagement()
    {
        $client = self::getDictionaryClient();
        $objectID = self::randomString();

        $searchResponse = $client->searchDictionaryEntries('plurals', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));

        $entry = array(
            'objectID' => $objectID,
            'language' => 'fr',
            'words' => array(
                'cheval', 'chevaux',
            ),
        );

        $client->saveDictionaryEntries(
            'plurals',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('plurals', $objectID);

        $this->assertCount(
            1,
            $addedEntries = $this->findEntriesWithObjectID($objectID, $searchResponse['hits'])
        );

        $savedEntry = $addedEntries[0];

        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['words'], $savedEntry['words']);

        $client->deleteDictionaryEntries('plurals', array($objectID))->wait();

        $searchResponse = $client->searchDictionaryEntries('plurals', $objectID);
        $this->assertCount(0, $this->findEntriesWithObjectID($objectID, $searchResponse['hits']));
    }

    private function findEntriesWithObjectID($objectID, $entries)
    {
        return array_values(
            array_filter(
                $entries,
                function ($hit) use ($objectID) {
                    return $hit['objectID'] == $objectID;
                }
            )
        );
    }
}

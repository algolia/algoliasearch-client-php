<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchClient;

class DictionaryManagementTest extends AlgoliaIntegrationTestCase
{
    /** @var SearchClient */
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
        $objectID = $stopWord = self::randomString();

        $initialStopwordCount = $client->searchDictionaryEntries('stopwords', $stopWord)['nbHits'];

        $entry = array('objectID' => $objectID, 'language' => 'en', 'word' => $stopWord);
        $client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $stopWord);
        $savedEntry = $searchResponse['hits'][0];

        $this->assertEquals($initialStopwordCount + 1, $searchResponse['nbHits']);
        $this->assertEquals($objectID, $savedEntry['objectID']);
        $this->assertEquals($stopWord, $savedEntry['word']);

        $client->deleteDictionaryEntries('stopwords', array($objectID))->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', $stopWord);

        $this->assertEquals($initialStopwordCount, $searchResponse['nbHits']);

        $oldDictionaryState = $client->searchDictionaryEntries('stopwords', '');
        $oldDictionaryEntries = array_map(function ($hit) {
            unset($hit['type']);

            return $hit;
        }, $oldDictionaryState['hits']);

        $client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', '');
        $this->assertEquals($oldDictionaryState['nbHits'] + 1, $searchResponse['nbHits']);

        $client->replaceDictionaryEntries('stopwords', $oldDictionaryEntries)->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', '');
        $this->assertEquals($oldDictionaryState['nbHits'], $searchResponse['nbHits']);

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

        $initialStopwordCount = $client->searchDictionaryEntries('compounds', 'kopfschmerztablette')['nbHits'];

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

        $searchResponse = $client->searchDictionaryEntries('compounds', 'kopfschmerztablette');
        $savedEntry = $searchResponse['hits'][0];

        $this->assertEquals($initialStopwordCount + 1, $searchResponse['nbHits']);
        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);
        $this->assertEquals($entry['decomposition'], $savedEntry['decomposition']);

        $client->deleteDictionaryEntries('compounds', array($objectID))->wait();

        $searchResponse = $client->searchDictionaryEntries('compounds', 'kopfschmerztablette');

        $this->assertEquals($initialStopwordCount, $searchResponse['nbHits']);
    }
}

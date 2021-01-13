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

    public function testDictionaryManagement()
    {
        $client = self::getDictionaryClient();

        /* We start with resetting the state of the dictionaries  */
        $client->clearDictionaryEntries('stopwords')->wait();
        $client->clearDictionaryEntries('plurals')->wait();
        $client->clearDictionaryEntries('compounds')->wait();

        $initialStopwordCount = $client->searchDictionaryEntries('stopwords', 'down')['nbHits'];

        $entry = array('objectID' => '1', 'language' => 'en', 'word' => 'down');
        $client->saveDictionaryEntries(
            'stopwords',
            array($entry)
        )->wait();

        $searchResponse = $client->searchDictionaryEntries('stopwords', 'down');
        $savedEntry = $searchResponse['hits'][0];

        $this->assertEquals($initialStopwordCount + 1, $searchResponse['nbHits']);
        $this->assertEquals($entry['objectID'], $savedEntry['objectID']);
        $this->assertEquals($entry['word'], $savedEntry['word']);

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
}

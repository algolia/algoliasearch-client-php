<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

class SearchClientTest extends AbstractMockClientTest
{
    public function testSaveDictionaryEntries()
    {
        $client = $this->getClient();
        $client->saveDictionaryEntries('stopwords', [['objectID' => 'abcdef', 'language' => 'en', 'word' => 'down']]);

        $this->assertRequests($this->loadRequestsForMethod('saveDictionaryEntries'));
    }

    public function testSearchDictionaryEntries()
    {
        $client = $this->getClient();
        $client->searchDictionaryEntries('stopwords', 'query');

        $this->assertRequests($this->loadRequestsForMethod('searchDictionaryEntries'));
    }

    public function testDeleteDictionaryEntries()
    {
        $client = $this->getClient();
        $client->deleteDictionaryEntries('stopwords', ['abcdef']);

        $this->assertRequests(
            $this->loadRequestsForMethod('deleteDictionaryEntries')
        );
    }

    public function testClearDictionaryEntries()
    {
        $client = $this->getClient();
        $client->clearDictionaryEntries('stopwords');

        $this->assertRequests(
            $this->loadRequestsForMethod('clearDictionaryEntries')
        );
    }

    public function testReplaceDictionaryEntries()
    {
        $client = $this->getClient();
        $client->replaceDictionaryEntries('stopwords', [['objectID' => 'xyz', 'language' => 'en', 'word' => 'down']]);

        $this->assertRequests(
            $this->loadRequestsForMethod('replaceDictionaryEntries')
        );
    }

    public function testSetDictionarySettings()
    {
        $client = $this->getClient();
        $client->setDictionarySettings([
            'disableStandardEntries' => [
                'stopwords' => [
                    'en' => true,
                ],
            ],
        ]);

        $this->assertRequests(
            $this->loadRequestsForMethod('setDictionarySettings')
        );
    }

    public function testGetDictionarySettings()
    {
        $client = $this->getClient();
        $client->getDictionarySettings();

        $this->assertRequests(
            $this->loadRequestsForMethod('getDictionarySettings')
        );
    }

    private function loadRequestsForMethod(string $method): array
    {
        $request = json_decode(file_get_contents("./requests_spec/SearchClient/${method}.json"), true);

        return $request['requests'];
    }
}

<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Tests\TestCase;
use PHPUnit\Framework\Constraint\IsInstanceOf;

class BasicTest extends TestCase
{
    public function testClientCanBeBuilt()
    {
        $client = $this->getClient();

        $this->assertThat($client, new IsInstanceOf(Client::class));
    }

    public function testClientIsAbleToListIndices()
    {
        $client = $this->getClient();

        $response = $client->listIndices();
        $this->assertTrue(is_array($response));
    }

    public function testIndexCanIndexThings()
    {
        $client = $this->getClient();
        $indexName = 'really_cool_test_'.rand(0, 20);
        $index = $client->index($indexName);
        $index->addObjects([['name' => 'fleur'], ['name' => 'orange'], ['name' => 'chien']]);

        $client->copyIndex($indexName, $indexName.'_COPY', [], [
            'destination' => $indexName.'_wrong_name'
        ]);
    }

    public function example($settings)
    {
        $client = Client::create(
            getenv('ALGOLIA_APP_ID'), getenv('ALGOLIA_API_KEY')
        );

        $response = $client->setSettings($settings, true, [
            'timeout' => 25,
            'clickAnalytics' => false,
            'X-CUSTOM-HEADER' => 'hop là',
        ]);

        dump($response);
    }

    public function alternativeExample($settings)
    {
        $client = Client::create(
            getenv('ALGOLIA_APP_ID'), getenv('ALGOLIA_API_KEY')
        );

        $response = $client
            ->setSettings($settings)
            ->forwardToReplica()
            ->with([
                'timeout' => 25,
                'clickAnalytics' => true,
                'X-CUSTOM-HEADER' => 'hop là',
            ])->send();

        dump($response);
    }

    public function testClearSynonyms()
    {
        $index = $this->getClient()->index('really_cool_test_Synonyms');

        $response = $index->clearSynonyms(false, [
            'forwardToReplicas' => true,
        ]);

        dump($response);
    }

    public function testKeysCanBeCreated()
    {
        $client = $this->getClient();

        $response = $client->addApiKey([
            'acl' => ['search', 'settings'],
            'validity' => 300,
        ]);

        dump($response);
    }
}

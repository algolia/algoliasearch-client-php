<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Client;
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
        $index = $this->getClient()->index('really_cool_test');
        $index->addObjects([['name' => 'fleur'], ['name' => 'orange'], ['name' => 'chien']]);
    }
}

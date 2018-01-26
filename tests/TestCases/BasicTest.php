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

        $response2 = $client->listIndices();
        $this->assertTrue(is_array($response2));
    }
}

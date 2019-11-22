<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\RecommendationClient;
use PHPUnit\Framework\TestCase;

class RecommendationClientTest extends TestCase
{
    public function testGetPersonalizationStrategy()
    {
        $recommendationClient = RecommendationClient::create('eu');

        $strategy = $recommendationClient->getPersonalizationStrategy();

        $this->assertArrayHasKey('eventsScoring', $strategy);
        $this->assertArrayHasKey('eventName', $strategy['eventsScoring'][0]);
        $this->assertArrayHasKey('eventType', $strategy['eventsScoring'][0]);
        $this->assertArrayHasKey('score', $strategy['eventsScoring'][0]);
        $this->assertArrayHasKey('facetsScoring', $strategy);
        $this->assertArrayHasKey('facetName', $strategy['facetsScoring'][0]);
        $this->assertArrayHasKey('score', $strategy['facetsScoring'][0]);
        $this->assertArrayHasKey('personalizationImpact', $strategy);
    }
}

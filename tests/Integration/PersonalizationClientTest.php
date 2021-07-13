<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\PersonalizationClient;

class PersonalizationClientTest extends BaseTest
{
    public function testRecommendationClient()
    {
        $personalizationClient = PersonalizationClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $strategy = [
            'eventsScoring' => [
                ['eventName' => 'Add to cart', 'eventType' => 'conversion', 'score' => 50],
                ['eventName' => 'Purchase', 'eventType' => 'conversion', 'score' => 100],
            ],
            'facetsScoring' => [
                ['facetName' => 'brand', 'score' => 100],
                ['facetName' => 'categories', 'score' => 10],
            ],
            'personalizationImpact' => 0,
        ];

        try {
            $personalizationClient->setPersonalizationStrategy($strategy);
        } catch (\Exception $e) {
            $this->assertEquals(429, $e->getCode());
        }

        $fetchedStrategy = $personalizationClient->getPersonalizationStrategy();

        $this->assertEquals($strategy, $fetchedStrategy);
    }
}

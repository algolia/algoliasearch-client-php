<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\RecommendationClient;

class RecommendationClientTest extends BaseTest
{
    public function testRecommendationClient()
    {
        $recommendationClient = RecommendationClient::create(
            getenv('ALGOLIA_APPLICATION_ID_1'),
            getenv('ALGOLIA_ADMIN_KEY_1')
        );

        $strategy = array(
            'eventsScoring' => array(
                array('eventName' => 'Add to cart', 'eventType' => 'conversion', 'score' => 50),
                array('eventName' => 'Purchase', 'eventType' => 'conversion', 'score' => 100),
            ),
            'facetsScoring' => array(
                array('facetName' => 'brand', 'score' => 100),
                array('facetName' => 'categories', 'score' => 10),
            ),
            'personalizationImpact' => 0,
        );

        try {
            $recommendationClient->setPersonalizationStrategy($strategy);
        } catch (\Exception $e) {
            $this->assertEquals(429, $e->getCode());
        }

        $fetchedStrategy = $recommendationClient->getPersonalizationStrategy();

        $this->assertEquals($strategy, $fetchedStrategy);
    }
}

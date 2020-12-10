<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\RecommendationClient;
use PHPUnit\Framework\TestCase;

class PersonalizationStrategyTest extends TestCase
{
    public function testPersonalizationStrategy()
    {
        $recommendationClient = RecommendationClient::create();

        $personalizationStrategy = array(
            'eventsScoring' => array(
                array(
                    'eventName' => 'Add to cart',
                    'eventType' => 'conversion',
                    'score' => 50,
                ),
                array(
                    'eventName' => 'Purchase',
                    'eventType' => 'conversion',
                    'score' => 100,
                ),
            ),
            'facetsScoring' => array(
                array('facetName' => 'brand', 'score' => 100),
                array('facetName' => 'categories', 'score' => 10),
            ),
            'personalizationImpact' => 0,
        );

        try {
            $response = $recommendationClient->setPersonalizationStrategy($personalizationStrategy);

            $this->assertEquals($response, array(
                'status' => 200,
                'message' => 'Strategy was successfully updated',
            ));

            $response = $recommendationClient->getPersonalizationStrategy();

            $this->assertEquals($personalizationStrategy, $response);
        } catch (BadRequestException $e) {
            if (429 !== $e->getCode()) {
                throw $e;
            }
        }
    }
}

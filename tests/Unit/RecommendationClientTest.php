<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\RecommendationClient;

class RecommendationClientTest extends RequestTestCase
{
    /** @var RecommendationClient */
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$client = RecommendationClient::create('eu');
    }

    public function testSetPersonalizationStrategyFailsWithoutEventsScoring()
    {
        try {
            $strategy = array(
                'facetsScoring' => array(
                    array(
                        'facetName' => 'brand',
                        'score' => 10,
                    ),
                ),
                'personalizationImpact' => 75,
            );

            self::$client->setPersonalizationStrategy($strategy);
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testSetPersonalizationStrategyFailsWithoutFacetsScoring()
    {
        try {
            $strategy = array(
                'eventsScoring' => array(
                    array(
                        'eventName' => 'buy',
                        'eventType' => 'conversion',
                        'score' => 10,
                    ),
                    array(
                        'eventName' => 'add to cart',
                        'eventType' => 'conversion',
                        'score' => 20,
                    ),
                ),
                'personalizationImpact' => 75,
            );

            self::$client->setPersonalizationStrategy($strategy);
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testSetPersonalizationStrategy()
    {
        $strategy = array(
            'eventsScoring' => array(
                array(
                    'eventName' => 'buy',
                    'eventType' => 'conversion',
                    'score' => 10,
                ),
                array(
                    'eventName' => 'add to cart',
                    'eventType' => 'conversion',
                    'score' => 20,
                ),
            ),
            'facetsScoring' => array(
                array(
                    'facetName' => 'brand',
                    'score' => 10,
                ),
            ),
            'personalizationImpact' => 75,
        );

        try {
            self::$client->setPersonalizationStrategy($strategy);
        } catch (\Exception $e) {
            $requestBody = json_decode((string) $e->getRequest()->getBody(), true);
            $this->assertEquals($requestBody, $strategy);
        }
    }
}

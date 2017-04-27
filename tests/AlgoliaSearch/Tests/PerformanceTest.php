<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class PerformanceTest extends AlgoliaSearchTestCase
{
    /** @var  Client */
    private $client;

    /** @var Index */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testAverageRequestTimeUnder50ms()
    {
        $start = microtime(true);

        $iterations = 20;
        for($i = 0; $i < $iterations; $i++) {
            $this->client->listIndexes();
        }

        $time = round(((microtime(true) - $start) * 1000)/$iterations);

        $this->assertLessThan( 50, $time );
    }

    public function testFirstRequestTimeUnder500ms()
    {
        $start = microtime(true);

        $this->client->listIndexes();
        
        $time = round(((microtime(true) - $start) * 1000));

        $this->assertLessThan( 300, $time );
    }
}

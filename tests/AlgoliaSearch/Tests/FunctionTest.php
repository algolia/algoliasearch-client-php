<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class FunctionTest extends AlgoliaSearchTestCase
{
    private $client;
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        $res = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($res['taskID']);
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function testKeepAlive()
    {
        if (getenv('TRAVIS_PHP_VERSION') == 'hhvm') {
            $this->markTestSkipped('Irrevelant on travis');
        }
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $init = $this->microtime_float();
        $this->client->listIndexes();
        $end_init = $this->microtime_float();
        for ($i = 1; $i <= 10; $i++) {
            $this->client->listIndexes();
        }
        $end = $this->microtime_float();
        $timeInit = ($end_init - $init);
        $timeAfter = ($end - $end_init) / 10;
        $this->assertGreaterThan(2.0, $timeInit / $timeAfter);
    }

    public function testConstructAPIKey()
    {
        $this->setExpectedException('Exception');
        new Client(getenv('ALGOLIA_APPLICATION_ID'), null);
    }

    public function testConstructAPPID()
    {
        $this->setExpectedException('Exception');
        new Client(null, getenv('ALGOLIA_API_KEY'));
    }

    public function testConstructHost()
    {
        $this->setExpectedException('Exception');
        $host = array('toto');
        $this->badClient = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), $host);
        $this->badIndex = $this->badClient->initIndex($this->safe_name('àlgol?à-php'));
        $res = $this->badIndex->addObject(array('firstname' => 'Robin'));
        $this->badIndex->waitTask($res['taskID']);
    }

    public function testBadAPPIP()
    {
        $this->setExpectedException('Exception');
        $this->badClient = new Client(getenv('ALGOLIA_APPLICATION_ID'), 'toto');
        $this->index = $this->badClient->listIndexes();
    }

    public function testFunction()
    {
        $this->client->disableRateLimitForward();
        $this->client->listIndexes();
    }

    public function microtime_float()
    {
        list($usec, $sec) = explode(' ', microtime());

        return (float) $usec + (float) $sec;
    }
}

<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class LogTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));  
    }

    public function testLogIndex()
    {
        $res = $this->client->getLogs();
        
        $this->assertGreaterThan(0, count($res['logs']));

        
    }

    private $client;
    private $index;
}

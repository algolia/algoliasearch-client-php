<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));  
        $this->index = $this->client->initIndex(safe_name('GetTest'));
        $res = $this->index->addObject(array("firstname" => "Robin"));
        $this->index->waitTask($res['taskID']);
    }

    public function testFunction()
    {
        $res = $this->client->listUserKeys();
        $res = $this->index->listUserKeys();
        $res = $this->index->getSettings();
        
    }

    private $client;
    private $index;
}

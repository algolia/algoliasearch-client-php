<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class ListIndexesTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
        try {
            $task = $this->client->deleteIndex(safe_name('àlgol?à2-php'));
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // ListTest2 does not exist
        }
    }

    public function tearDown()
    {
        try {
            $this->client->deleteIndex(safe_name('àlgol?à-php'));
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
        try {
            $this->client->deleteIndex(safe_name('àlgol?à2-php'));
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }

    }

    public function containsValue($array, $attr, $value)
    {
      foreach ($array as $elt)
      {
        if ($elt[$attr] == $value)
        {
          return true;
        }
      }
      return false;
    }

    public function testListIndexes()
    {
        $this->index2 = $this->client->initIndex(safe_name('ListTest2'));
        $task = $this->index2->addObject(array("firstname" => "Robin"));
        $this->index2->waitTask($task['taskID']);
        $resAfter = $this->client->listIndexes();

        $this->assertTrue($this->containsValue($resAfter["items"], "name", safe_name('ListTest2')));
        
    }

    private $client;
    private $index;
}

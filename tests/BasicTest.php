<?php

@include __DIR__ . '/../vendor/autoload.php';

function safe_name($name) {
    if (getenv("TRAVIS") != "true") {
        return $name;
    }
    $id = end(explode(".", getenv("TRAVIS_JOB_NUMBER")));
    return $name . "_travis-" . $id;
}

class BasicTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('friends_php2'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function testAddObject()
    {
        $res = $this->index->addObject(array("firstname" => "Robin"));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('');
        $this->assertEquals(1, $results['nbHits']);
        $this->assertEquals('Robin', $results['hits'][0]['firstname']);
    }

    public function testAddObjects()
    {
        $res = $this->index->addObjects(array(
            array("firstname" => "Robin"),
            array("firstname" => "Robert")
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    public function testSaveObjects()
    {
        $res = $this->index->saveObjects(array(
            array("firstname" => "Robin", "objectID" => 1),
            array("firstname" => "Robert", "objectID" => 2)
        ));
        $this->index->waitTask($res['taskID']);
        $results = $this->index->search('rob');
        $this->assertEquals(2, $results['nbHits']);
    }

    private $client;
    private $index;
}

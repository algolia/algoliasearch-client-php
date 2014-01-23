<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';


class SecurityTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \AlgoliaSearch\Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex(safe_name('SecurityTest'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaSearch\AlgoliaException $e) {
            // not fatal
        }
    }

    public function testSecurityIndex()
    {
        $res = $this->index->listUserKeys();
        $newKey = $this->index->addUserKey(['search']);
        $this->assertTrue($newKey['key'] != "");
        $resAfter = $this->index->listUserKeys();
        $this->assertEquals(count($res['keys']) + 1, count($resAfter['keys']));
        $key = $this->index->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'search');
        $task = $this->index->deleteUserKey($newKey['key']);
        $resEnd = $this->index->listUserKeys();
        $this->assertEquals(count($res['keys']), count($resEnd['keys']));

        $res = $this->client->listUserKeys();
        $newKey = $this->client->addUserKey(['search']);
        $this->assertTrue($newKey['key'] != "");
        $resAfter = $this->client->listUserKeys();
        $this->assertEquals(count($res['keys']) + 1, count($resAfter['keys']));
        $key = $this->client->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'search');
        $task = $this->client->deleteUserKey($newKey['key']);
        $resEnd = $this->client->listUserKeys();
        $this->assertEquals(count($res['keys']), count($resEnd['keys']));
    }

    private $client;
    private $index;
}

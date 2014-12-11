<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class SecurityTest extends AlgoliaSearchTestCase
{
    private $client;
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

    public function testSecurityIndex()
    {
        $res = $this->index->addObject(array("firstname" => "Robin"));
        $this->index->waitTask($res['taskID']);
        $res = $this->index->listUserKeys();
        $newKey = $this->index->addUserKey(array('search'));
        sleep(5);
        $this->assertTrue($newKey['key'] != "");
        $resAfter = $this->index->listUserKeys();
        $this->assertTrue($this->containsValue($resAfter["keys"], "value", $newKey['key']));
        $this->assertFalse($this->containsValue($res["keys"], "value", $newKey['key']));
        $key = $this->index->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'search');
        $this->index->updateUserKey($newKey['key'], array('addObject'));
        sleep(5);
        $key = $this->index->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'addObject');
        $task = $this->index->deleteUserKey($newKey['key']);
        sleep(5);
        $resEnd = $this->index->listUserKeys();
        $this->assertFalse($this->containsValue($resEnd["keys"], "value", $newKey['key']));

        $res = $this->client->listUserKeys();
        $newKey = $this->client->addUserKey(array('search'));
        sleep(5);
        $this->assertTrue($newKey['key'] != "");
        $resAfter = $this->client->listUserKeys();
        $this->assertTrue($this->containsValue($resAfter["keys"], "value", $newKey['key']));
        $this->assertFalse($this->containsValue($res["keys"], "value", $newKey['key']));
        $key = $this->client->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'search');
        $this->client->updateUserKey($newKey['key'], array('addObject'));
        sleep(5);
        $key = $this->client->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'addObject');
        $task = $this->client->deleteUserKey($newKey['key']);
        sleep(5);
        $resEnd = $this->client->listUserKeys();
        $this->assertFalse($this->containsValue($resEnd["keys"], "value", $newKey['key']));
    }

    public function testSecurityMultipleIndices()
    {
        $a = $this->client->initIndex($this->safe_name('a-12'));
        $res = $a->setSettings(array('hitsPerPage' => 10));
        $a->waitTask($res['taskID']);
        $b = $this->client->initIndex($this->safe_name('b-13'));
        $res = $b->setSettings(array('hitsPerPage' => 10));
        $b->waitTask($res['taskID']);

        $newKey = $this->client->addUserKey(array('search', 'addObject', 'deleteObject'), 0, 0, 0, array($this->safe_name('a-12'), $this->safe_name('b-13')));
        sleep(5);
        $this->assertTrue($newKey['key'] != "");
        $res = $this->client->listUserKeys();
        $this->assertTrue($this->containsValue($res["keys"], "value", $newKey['key']));

        $this->client->deleteIndex($this->safe_name('a-12'));
        $this->client->deleteIndex($this->safe_name('b-13'));
    }

    public function testSecuredApiKeys()
    {
        $this->assertEquals('1fd74b206c64fb49fdcd7a5f3004356cd3bdc9d9aba8733656443e64daafc417', hash_hmac('sha256', '(public,user1)', 'my_api_key'));
        $key = $this->client->generateSecuredApiKey('my_api_key', '(public,user1)');
        $this->assertEquals($key, hash_hmac('sha256', '(public,user1)', 'my_api_key'));
        $key = $this->client->generateSecuredApiKey('my_api_key', '(public,user1)', 42);
        $this->assertEquals($key, hash_hmac('sha256', '(public,user1)42', 'my_api_key'));
        $key = $this->client->generateSecuredApiKey('my_api_key', array('public'));
        $this->assertEquals($key, hash_hmac('sha256', 'public', 'my_api_key'));
        $key = $this->client->generateSecuredApiKey('my_api_key', array('public', array('premium','vip')));
        $this->assertEquals($key, hash_hmac('sha256', 'public,(premium,vip)', 'my_api_key'));
    }
}

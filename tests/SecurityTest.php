<?php

include __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../algoliasearch.php';

class SecurityTest extends PHPUnit_Framework_TestCase
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
    }

    public function tearDown()
    {
        try {
            $this->client->deleteIndex(safe_name('àlgol?à-php'));
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

    public function testSecuredApiKeys()
    {
        $this->assertEquals('143fec7bef6f16f6aa127a4949948a966816fa154e67a811e516c2549dbe2a8b', hash('sha256', 'my_api_key(public,user1)'));
        $key = $this->client->generateSecuredApiKey('my_api_key', '(public,user1)');
        $this->assertEquals($key, hash('sha256', 'my_api_key(public,user1)'));
        $key = $this->client->generateSecuredApiKey('my_api_key', '(public,user1)', 42);
        $this->assertEquals($key, hash('sha256', 'my_api_key(public,user1)42'));
        $key = $this->client->generateSecuredApiKey('my_api_key', array('public'));
        $this->assertEquals($key, hash('sha256', 'my_api_keypublic'));
        $key = $this->client->generateSecuredApiKey('my_api_key', array('public', array('premium','vip')));
        $this->assertEquals($key, hash('sha256', 'my_api_keypublic,(premium,vip)'));
    }

    private $client;
    private $index;
}

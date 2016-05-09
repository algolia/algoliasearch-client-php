<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class SecurityTest extends AlgoliaSearchTestCase
{
	/** @var Client */
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

    public function testSecurityIndex()
    {
	    $timeout = 50;

        $res = $this->index->addObject(['firstname' => 'Robin']);
        $this->index->waitTask($res['taskID']);

        $res = $this->index->listUserKeys();
        $newKey = $this->index->addUserKey(['search']);

	    $this->assertTrue($newKey['key'] != '');
	    $this->assertFalse($this->containsValue($res['keys'], 'value', $newKey['key']));

	    $asserted = FALSE;
	    $timeouted = time() + $timeout;
	    while(time() < $timeouted)
	    {
		    $resAfter = $this->index->listUserKeys();

	        if($this->containsValue($resAfter['keys'], 'value', $newKey['key']) || time() >= $timeouted)
	        {
		        $asserted = TRUE;
		        $this->assertTrue($this->containsValue($resAfter['keys'], 'value', $newKey['key']));
		        break;
	        }

		    usleep(500);
	    }

	    if($asserted === FALSE)
	    {
		    $this->assertTrue($this->containsValue($resAfter['keys'], 'value', $newKey['key']));
	    }

        $key = $this->index->getUserKeyACL($newKey['key']);
        $this->assertEquals($key['acl'][0], 'search');
	    
        $this->index->updateUserKey($newKey['key'], ['addObject']);

	    $asserted = FALSE;
	    $timeouted = time() + $timeout;
	    while(time() < $timeouted)
	    {
		    $key = $this->index->getUserKeyACL($newKey['key']);

		    if($key['acl'][0] === 'addObject' || time() >= $timeouted)
		    {
			    $asserted = TRUE;
			    $this->assertEquals($key['acl'][0], 'addObject');
			    break;
		    }

		    usleep(500);
	    }

	    if($asserted === FALSE)
	    {
		    $this->assertEquals($key['acl'][0], 'addObject');
	    }
	    
        $this->index->deleteUserKey($newKey['key']);

	    $asserted = FALSE;
	    $timeouted = time() + $timeout;
	    while(time() < $timeouted)
	    {
		    $resEnd = $this->index->listUserKeys();

		    if($this->containsValue($resEnd['keys'], 'value', $newKey['key']) === FALSE || time() >= $timeouted)
		    {
			    $asserted = TRUE;
			    $this->assertFalse($this->containsValue($resEnd['keys'], 'value', $newKey['key']));
			    break;
		    }

		    usleep(500);
	    }

	    if($asserted === FALSE)
	    {
		    $this->assertFalse($this->containsValue($resEnd['keys'], 'value', $newKey['key']));
	    }
    }

    public function testSecurityMultipleIndices()
    {
        $a = $this->client->initIndex($this->safe_name('a-12'));
        $res = $a->setSettings(['hitsPerPage' => 10]);
        $a->waitTask($res['taskID']);
        $b = $this->client->initIndex($this->safe_name('b-13'));
        $res = $b->setSettings(['hitsPerPage' => 10]);
        $b->waitTask($res['taskID']);

        $newKey = $this->client->addUserKey(['search', 'addObject', 'deleteObject'], 0, 0, 0, [$this->safe_name('a-12'), $this->safe_name('b-13')]);
        sleep(5);
        $this->assertTrue($newKey['key'] != '');
        $res = $this->client->listUserKeys();
        $this->assertTrue($this->containsValue($res['keys'], 'value', $newKey['key']));

        $this->client->deleteIndex($this->safe_name('a-12'));
        $this->client->deleteIndex($this->safe_name('b-13'));
    }

    public function testNewSecuredApiKeys()
    {
        $this->assertEquals('MDZkNWNjNDY4M2MzMDA0NmUyNmNkZjY5OTMzYjVlNmVlMTk1NTEwMGNmNTVjZmJhMmIwOTIzYjdjMTk2NTFiMnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjk=', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', '(public,user1)'));
        $this->assertEquals('MDZkNWNjNDY4M2MzMDA0NmUyNmNkZjY5OTMzYjVlNmVlMTk1NTEwMGNmNTVjZmJhMmIwOTIzYjdjMTk2NTFiMnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjk=', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', ['tagFilters' => '(public,user1)']));
        $this->assertEquals('OGYwN2NlNTdlOGM2ZmM4MjA5NGM0ZmYwNTk3MDBkNzMzZjQ0MDI3MWZjNTNjM2Y3YTAzMWM4NTBkMzRiNTM5YnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjkmdXNlclRva2VuPTQy', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', ['tagFilters' => '(public,user1)', 'userToken' => '42']));
        $this->assertEquals('OGYwN2NlNTdlOGM2ZmM4MjA5NGM0ZmYwNTk3MDBkNzMzZjQ0MDI3MWZjNTNjM2Y3YTAzMWM4NTBkMzRiNTM5YnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjkmdXNlclRva2VuPTQy', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', ['tagFilters' => '(public,user1)'], '42'));
    }
}

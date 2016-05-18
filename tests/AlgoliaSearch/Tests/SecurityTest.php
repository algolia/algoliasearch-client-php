<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class SecurityTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    public $client;

    /** @var Index */
    public $index;

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
        $res = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($res['taskID']);

        $res = $this->index->listUserKeys();
        $newKey = $this->index->addUserKey(array('search'));

        $this->assertTrue($newKey['key'] != '');
        $this->assertFalse($this->containsValue($res['keys'], 'value', $newKey['key']));

        $self = $this;
        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $resAfter = $self->index->listUserKeys();

            if ($self->containsValue($resAfter['keys'], 'value', $newKey['key']) || time() >= $timeouted) {
                $self->assertTrue($self->containsValue($resAfter['keys'], 'value', $newKey['key']));

                return true;
            }

            return false;
        });

        $key = $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            try {
                $key = $self->index->getUserKeyACL($newKey['key']);

                return $key;
            } catch (AlgoliaException $e) {
                if (time() >= $timeouted) {
                    throw $e;
                }
            }

            return false;
        });

        $this->assertEquals($key['acl'][0], 'search');

        $this->index->updateUserKey($newKey['key'], array('addObject'));

        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            try {
                $key = $self->index->getUserKeyACL($newKey['key']);

                if ($key['acl'][0] === 'addObject' || time() >= $timeouted) {
                    $self->assertEquals($key['acl'][0], 'addObject');

                    return true;
                }
            } catch (AlgoliaException $e) {
                if (time() >= $timeouted) {
                    throw $e;
                }
            }

            return false;
        });

        $this->index->deleteUserKey($newKey['key']);

        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $resEnd = $self->index->listUserKeys();

            if ($self->containsValue($resEnd['keys'], 'value', $newKey['key']) === false || time() >= $timeouted) {
                $self->assertFalse($self->containsValue($resEnd['keys'], 'value', $newKey['key']));

                return true;
            }

            return false;
        });
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
        $this->assertTrue($newKey['key'] != '');

        $self = $this;
        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $res = $self->client->listUserKeys();

            if ($self->containsValue($res['keys'], 'value', $newKey['key']) || time() >= $timeouted) {
                $self->assertTrue($self->containsValue($res['keys'], 'value', $newKey['key']));

                return true;
            }

            return false;
        });

        $this->client->deleteIndex($this->safe_name('a-12'));
        $this->client->deleteIndex($this->safe_name('b-13'));
    }

    public function testNewSecuredApiKeys()
    {
        $this->assertEquals('MDZkNWNjNDY4M2MzMDA0NmUyNmNkZjY5OTMzYjVlNmVlMTk1NTEwMGNmNTVjZmJhMmIwOTIzYjdjMTk2NTFiMnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjk=', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', '(public,user1)'));
        $this->assertEquals('MDZkNWNjNDY4M2MzMDA0NmUyNmNkZjY5OTMzYjVlNmVlMTk1NTEwMGNmNTVjZmJhMmIwOTIzYjdjMTk2NTFiMnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjk=', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', array('tagFilters' => '(public,user1)')));
        $this->assertEquals('OGYwN2NlNTdlOGM2ZmM4MjA5NGM0ZmYwNTk3MDBkNzMzZjQ0MDI3MWZjNTNjM2Y3YTAzMWM4NTBkMzRiNTM5YnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjkmdXNlclRva2VuPTQy', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', array('tagFilters' => '(public,user1)', 'userToken' => '42')));
        $this->assertEquals('OGYwN2NlNTdlOGM2ZmM4MjA5NGM0ZmYwNTk3MDBkNzMzZjQ0MDI3MWZjNTNjM2Y3YTAzMWM4NTBkMzRiNTM5YnRhZ0ZpbHRlcnM9JTI4cHVibGljJTJDdXNlcjElMjkmdXNlclRva2VuPTQy', $this->client->generateSecuredApiKey('182634d8894831d5dbce3b3185c50881', array('tagFilters' => '(public,user1)'), '42'));
    }

    private function poolingTask(\Closure $callback, $timeout = 200)
    {
        $timeouted = time() + $timeout;
        while (time() < $timeouted) {
            sleep(1);

            $res = $callback($timeouted);
            if ($res !== false) {
                return $res;
            }
        }

        return;
    }
}

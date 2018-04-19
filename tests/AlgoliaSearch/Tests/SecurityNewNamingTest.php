<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class SecurityNewNamingTest extends AlgoliaSearchTestCase
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

    public function testNewApiKey()
    {
        $createdKeys = array();

        $createdKeys[] = $res = $this->client->addApiKey(array('search'), 1000, 12, 2);
        $simpleKey = $this->getKey($res['key']);

        $createdKeys[] = $res = $this->client->addApiKey(array(
            'acl' => array('search'),
            'validity' => 1000,
            'maxQueriesPerIPPerHour' => 12,
            'maxHitsPerQuery' => 2,
        ));
        $objKey = $this->getKey($res['key']);

        $this->assertGreaterThan(500, $simpleKey['validity']);
        $this->assertGreaterThan(500, $objKey['validity']);

        $subArray = array(
            'acl' => array('search'),
            'maxQueriesPerIPPerHour' => 12,
            'maxHitsPerQuery' => 2,
        );
        $this->assertArraySubset($subArray, $simpleKey);
        $this->assertArraySubset($subArray, $objKey);

        $createdKeys[] = $res = $this->client->addApiKey(array(
            'acl' => array('search'),
            'validity' => 300,
            'maxQueriesPerIPPerHour' => 200,
            'maxHitsPerQuery' => 321,
        ), 1000, 12, 2);
        $priorityKey = $this->getKey($res['key']);

        $this->assertGreaterThan(301, $priorityKey[ 'validity']);
        $this->assertArraySubset($subArray, $priorityKey);

        $createdKeys[] = $res = $this->client->addApiKey(array(
            'acl' => array('search'),
            'maxHitsPerQuery' => 23,
        ));
        $mixKey = $this->getKey($res['key']);

        // Validity will be set to 0 but maxQueriesPerIPPerHour won' exist
        $this->assertArraySubset(array(
            'acl' => array('search'),
            'validity' => 0,
            'maxHitsPerQuery' => 23,
        ), $mixKey);

        // Delete them all
        foreach ($createdKeys as $key) {
            $this->client->deleteApiKey($key['key']);
        }
    }

    public function testUpdateApiKey()
    {
        $createdKeys = array();

        $createdKeys[] = $res = $this->client->addApiKey(array('search'), 1000, 12, 2);

        $originalKey = $this->getKey($res['key']);
        $this->assertLessThan(1000, $originalKey['validity']);

        $res = $this->client->updateApiKey($originalKey['value'], array(
            'acl' => array('search', 'browse'),
            'validity' => 2000,
            'maxQueriesPerIPPerHour' => 20,
            'maxHitsPerQuery' => 20,
        ));
        $updatedKey = $this->getUpdatedKey($res['key'], $originalKey);

        $subArray = array(
            'acl' => array('search', 'browse'),
            'maxQueriesPerIPPerHour' => 20,
            'maxHitsPerQuery' => 20,
        );
        $this->assertGreaterThan(1000, $updatedKey['validity']);
        $this->assertArraySubset($subArray, $updatedKey);

        // Delete them all
        foreach ($createdKeys as $key) {
            $this->client->deleteApiKey($key['key']);
        }
    }

    public function testSecurityIndex()
    {
        $res = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($res['taskID']);

        $res = $this->index->listApiKeys();
        $newKey = $this->index->addApiKey(array('search'));

        $this->assertNotSame('', $newKey['key']);
        $this->assertFalse($this->containsValue($res['keys'], 'value', $newKey['key']));

        $self = $this;
        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $resAfter = $self->index->listApiKeys();

            if ($self->containsValue($resAfter['keys'], 'value', $newKey['key']) || time() >= $timeouted) {
                $self->assertTrue($self->containsValue($resAfter['keys'], 'value', $newKey['key']));

                return true;
            }

            return false;
        });

        $key = $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            try {
                $key = $self->index->getApiKey($newKey['key']);

                return $key;
            } catch (AlgoliaException $e) {
                if (time() >= $timeouted) {
                    throw $e;
                }
            }

            return false;
        });

        $this->assertEquals($key['acl'][0], 'search');

        $this->index->updateApiKey($newKey['key'], array('addObject'));

        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            try {
                $key = $self->index->getApiKey($newKey['key']);

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

        $this->index->deleteApiKey($newKey['key']);

        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $resEnd = $self->index->listApiKeys();

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

        $newKey = $this->client->addApiKey(array('search', 'addObject', 'deleteObject'), 0, 0, 0, array($this->safe_name('a-12'), $this->safe_name('b-13')));
        $this->assertNotSame('', $newKey['key']);

        $self = $this;
        $this->poolingTask(function ($timeouted) use ($newKey, $self) {
            $res = $self->client->listApiKeys();

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

    private function getKey($key)
    {
        $loop = 0;
        do {
            $loop++;
            sleep(1);
            $list = $this->client->listApiKeys();

            foreach ($list['keys'] as $item) {
                if ($item['value'] == $key) {
                    return $item;
                }
            }
        } while ($loop < 10);
    }

    private function getUpdatedKey($key, $original)
    {
        // Validity change every second, it cannot be compared
        unset($original['validity']);
        $loop = 0;
        do {
            $loop++;
            sleep(1);
            $updatedKey = $this->client->getApiKey($key);

            try{
                // Validity change every second, it cannot be compared
                $tempWithoutValidity = $updatedKey;
                unset($tempWithoutValidity['validity']);
                $this->assertArraySubset($tempWithoutValidity, $original);
            } catch (\Exception $e) {
                // The key was updated
                return $updatedKey;
            }
        } while ($loop < 10);

        throw new \Exception('The key could not be updated.');
    }
}

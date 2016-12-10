<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\FailingHostsCache;

abstract class FailingHostsCacheTestCase extends \PHPUnit_Framework_TestCase
{
    public function testShouldDeduplicateFailingHosts()
    {
        $cache = $this->getNewCleanCacheInstance();

        $cache->addFailingHost('host1.com');
        $cache->addFailingHost('host2.com');
        $cache->addFailingHost('host1.com');
        $cache->addFailingHost('host3.com');
        $cache->addFailingHost('host2.com');

        $this->assertEquals(array('host1.com', 'host2.com', 'host3.com'), $cache->getFailingHosts());
    }

    public function testCacheCanBeInvalidated()
    {
        $cache = $this->getNewCacheInstance();
        $cache->addFailingHost('host1.com');

        $cache->flushFailingHostsCache();

        $this->assertEquals(array(), $cache->getFailingHosts());
    }

    /**
     * @depends testCacheCanBeInvalidated
     */
    public function testShouldShareFailingHostsBetweenInstances()
    {
        $cache = $this->getNewCleanCacheInstance();
        
        $cache->addFailingHost('host1.com');

        $cache2 = $this->getNewCacheInstance();
        $cache2->addFailingHost('host2.com');
        
        // The 2 instances should have the same state.
        $this->assertEquals(array('host1.com', 'host2.com'), $cache->getFailingHosts());
        $this->assertEquals(array('host1.com', 'host2.com'), $cache2->getFailingHosts());
        
        // Clean the state.
        $cache2->flushFailingHostsCache();

        // Ensure state is cleaned in both instances.
        $this->assertEquals(array(), $cache->getFailingHosts());
        $this->assertEquals(array(), $cache2->getFailingHosts());
    }

    public function testShouldInvalidateWhenTtlIsReached()
    {
        $cache = $this->getNewCleanCacheInstance();

        $cache->addFailingHost('host1.com');

        $this->assertEquals(array('host1.com'), $cache->getFailingHosts());

        sleep(3);

        $this->assertEquals(array(), $cache->getFailingHosts());
    }

    /**
     * @param int $ttl
     *
     * @return FailingHostsCache
     */
    protected function getNewCleanCacheInstance($ttl = 2)
    {
        $cache = $this->getNewCacheInstance($ttl);
        $cache->flushFailingHostsCache();

        return $cache;
    }

    /**
     * @param int $ttl
     *
     * @return FailingHostsCache
     */
    abstract public function getNewCacheInstance($ttl = 2);
}

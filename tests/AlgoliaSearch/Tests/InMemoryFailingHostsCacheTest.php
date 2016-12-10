<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\InMemoryFailingHostsCache;

class InMemoryFailingHostsCacheTest extends FailingHostsCacheTestCase
{
    /**
     * @param int $ttl
     *
     * @return FailingHostsCache
     */
    public function getNewCacheInstance($ttl = 2)
    {
        return new InMemoryFailingHostsCache($ttl);
    }
}

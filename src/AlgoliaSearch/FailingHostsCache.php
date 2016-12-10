<?php
namespace AlgoliaSearch;

interface FailingHostsCache
{
    /**
     * @param string $host
     */
    public function addFailingHost($host);

    /**
     * Get failing hosts from cache. This method should also handle cache invalidation if required.
     * The TTL of the failed hosts cache should be 5 minutes.
     *
     * @return array
     */
    public function getFailingHosts();

    /**
     * Invalidates the cache.
     */
    public function flushFailingHostsCache();
}

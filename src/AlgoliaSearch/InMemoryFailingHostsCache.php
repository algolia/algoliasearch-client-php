<?php

namespace AlgoliaSearch;

class InMemoryFailingHostsCache implements FailingHostsCache
{

    /**
     * @var array
     */
    private static $failingHosts = array();

    /**
     * @var int
     */
    private static $timestamp;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @param int|null $ttl The time to live of the cache in seconds.
     */
    public function __construct($ttl = null)
    {
        if ($ttl === null) {
            $ttl = 60 * 5; // 5 minutes
        }
        
        $this->ttl = (int) $ttl;
    }


    /**
     * @param string $host
     */
    public function addFailingHost($host)
    {
        if (! in_array($host, self::$failingHosts)) {
            // Keep a local cache of failed hosts in case the file based strategy doesn't work out.
            self::$failingHosts[] = $host;

            if (self::$timestamp === null) {
                self::$timestamp = time();
            }
        }
    }

    /**
     * Get failing hosts from cache. This method should also handle cache invalidation if required.
     * The TTL of the failed hosts cache should be 5mins.
     *
     * @return array
     */
    public function getFailingHosts()
    {
        if (self::$timestamp === null) {
            return self::$failingHosts;
        }

        $elapsed = time() - self::$timestamp;
        if ($elapsed > $this->ttl) {
            $this->flushFailingHostsCache();
        }

        return self::$failingHosts;
    }
    
    public function flushFailingHostsCache()
    {
        self::$failingHosts = array();
        self::$timestamp = null;
    }
}

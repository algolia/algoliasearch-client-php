<?php

namespace AlgoliaSearch;

class FileFailingHostsCache implements FailingHostsCache
{

    /**
     * @var string
     */
    private $failingHostsCacheFile;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @param int|null    $ttl The time to live of the cache in seconds.
     * @param string|null $file
     *
     */
    public function __construct($ttl = null, $file = null)
    {
        if (null === $file) {
            $this->failingHostsCacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'algolia-failing-hosts';
        } else {
            $this->failingHostsCacheFile = (string)$file;
        }

        $directory = dirname($this->failingHostsCacheFile);
        if (! is_writable($directory)) {
            throw new \RuntimeException(sprintf('Cache file directory "%s" is not writable.', $directory));
        }

        if (null === $ttl) {
            $ttl = 60 * 5; // 5 minutes
        }

        $this->ttl = (int) $ttl;
    }


    /**
     * @param string $host
     */
    public function addFailingHost($host)
    {
        @include $this->failingHostsCacheFile;
        if (isset($ttl) && isset($failingHosts)) {
            // Update failing hosts cache.
            // Here we don't take care of invalidating. We do that on retrieval.
            if (!in_array($host, $failingHosts)) {
                $failingHosts[] = $host;
                file_put_contents(
                    $this->failingHostsCacheFile,
                    '<?php $ttl = ' . $ttl . '; $failingHosts = ' . var_export($failingHosts, true) . ';'
                );
                $this->invalidateOpcache();
            }
        } else {
            file_put_contents(
                $this->failingHostsCacheFile,
                '<?php $ttl = ' . time() . '; $failingHosts = ' . var_export(array($host), true) . ';'
            );
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
        @include $this->failingHostsCacheFile;

        if (!isset($ttl) || !isset($failingHosts)) {
            return array();
        }

        $elapsed = time() - $ttl; // Number of seconds elapsed.

        if ($elapsed > $this->ttl) {
            $this->flushFailingHostsCache();

            return array();
        }

        return $failingHosts;
    }

    private function invalidateOpcache()
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->failingHostsCacheFile, true);
        }
    }

    public function flushFailingHostsCache()
    {
        $this->invalidateOpcache();
        @unlink($this->failingHostsCacheFile);
    }
}

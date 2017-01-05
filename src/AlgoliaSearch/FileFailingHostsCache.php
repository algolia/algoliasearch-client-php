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
        $this->failingHostsCacheFile = null === $file ? $this->getDefaultCacheFile() : (string)$file;

        $this->assertCacheFileIsValid($file);

        if (null === $ttl) {
            $ttl = 60 * 5; // 5 minutes
        }

        $this->ttl = (int) $ttl;
    }

    /**
     * @param $file
     */
    private function assertCacheFileIsValid($file)
    {
        $fileDirectory = dirname($file);
        if (! is_writable($fileDirectory)) {
            throw new \RuntimeException(sprintf('Cache file directory "%s" is not writable.', $fileDirectory));
        }

        if (! file_exists($file)) {
            // The dir being writable, the file will be created when needed.
            return;
        }

        if (! is_readable($file)) {
            throw new \RuntimeException(sprintf('Cache file "%s" is not readable.', $file));
        }

        if (! is_writable($file)) {
            throw new \RuntimeException(sprintf('Cache file "%s" is not writable.', $file));
        }
    }

    /**
     * @return string
     */
    private function getDefaultCacheFile()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'algolia-failing-hosts';
    }

    /**
     * @param string $host
     */
    public function addFailingHost($host)
    {
        if (defined('HHVM_VERSION')) {
            // workaround for https://github.com/facebook/hhvm/issues/1447
            $content = @file_get_contents($this->failingHostsCacheFile);
            if ($content !== false) {
                eval(str_replace('<?php', '', file_get_contents($this->failingHostsCacheFile)));
            }
        } else {
            @include $this->failingHostsCacheFile;
        }

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
        if (defined('HHVM_VERSION')) {
            // workaround for https://github.com/facebook/hhvm/issues/1447
            $content = @file_get_contents($this->failingHostsCacheFile);
            if ($content !== false) {
                eval(str_replace('<?php', '', file_get_contents($this->failingHostsCacheFile)));
            }
        } else {
            @include $this->failingHostsCacheFile;
        }

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

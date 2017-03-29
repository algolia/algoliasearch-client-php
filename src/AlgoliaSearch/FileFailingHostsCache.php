<?php

namespace AlgoliaSearch;

class FileFailingHostsCache implements FailingHostsCache
{
    /**
     * Timestamp key used in the JSON representation.
     */
    const TIMESTAMP = 'timestamp';

    /**
     * Failing hosts key used in the JSON representation.
     */
    const FAILING_HOSTS = 'failing_hosts';

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
        if ($file === null) {
            $this->failingHostsCacheFile = $this->getDefaultCacheFile();
        } else {
            $this->failingHostsCacheFile = (string) $file;
        }

        $this->assertCacheFileIsValid($this->failingHostsCacheFile);

        if ($ttl === null) {
            $ttl = 60 * 5; // 5 minutes
        }

        $this->ttl = (int) $ttl;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
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
        $cache = $this->loadFailingHostsCacheFromDisk();

        if (isset($cache[self::TIMESTAMP]) && isset($cache[self::FAILING_HOSTS])) {
            // Update failing hosts cache.
            // Here we don't take care of invalidating. We do that on retrieval.
            if (!in_array($host, $cache[self::FAILING_HOSTS])) {
                $cache[self::FAILING_HOSTS][] = $host;
                $this->writeFailingHostsCacheFile($cache);
            }
        } else {
            $cache[self::TIMESTAMP] = time();
            $cache[self::FAILING_HOSTS] = array($host);
            $this->writeFailingHostsCacheFile($cache);
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
        $cache = $this->loadFailingHostsCacheFromDisk();

        return isset($cache[self::FAILING_HOSTS]) ? $cache[self::FAILING_HOSTS] : array();
    }

    /**
     * Removes the file storing the failing hosts.
     */
    public function flushFailingHostsCache()
    {
        if (file_exists($this->failingHostsCacheFile)) {
            unlink($this->failingHostsCacheFile);
        }
    }

    /**
     * @return array
     */
    private function loadFailingHostsCacheFromDisk()
    {
        if (! file_exists($this->failingHostsCacheFile)) {
            return array();
        }

        $json = file_get_contents($this->failingHostsCacheFile);
        if ($json === false) {
            return array();
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array();
        }

        // Some basic checks.
        if (
            !isset($data[self::TIMESTAMP])
            || !isset($data[self::FAILING_HOSTS])
            || !is_int($data[self::TIMESTAMP])
            || !is_array($data[self::FAILING_HOSTS])
        ) {
            return array();
        }

        // Validate the hosts array.
        foreach ($data[self::FAILING_HOSTS] as $host) {
            if (!is_string($host)) {
                return array();
            }
        }

        $elapsed = time() - $data[self::TIMESTAMP]; // Number of seconds elapsed.

        if ($elapsed > $this->ttl) {
            $this->flushFailingHostsCache();

            return array();
        }

        return $data;
    }

    /**
     * @param array $data
     */
    private function writeFailingHostsCacheFile(array $data)
    {
        $json = json_encode($data);
        if ($json !== false) {
            file_put_contents($this->failingHostsCacheFile, $json);
        }
    }
}

<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\FileFailingHostsCache;

class FileFailingHostsCacheTest extends FailingHostsCacheTestCase
{
    /**
     * @expectedException RuntimeException
     * @expectExceptionMessage is not writable
     */
    public function testShouldThrowAnExceptionIfCacheDirectoryIsNotWritable()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cache_dir';
        $cacheFile = $dir . DIRECTORY_SEPARATOR . 'cache_file';
        @unlink($cacheFile);
        @rmdir($dir);
        mkdir($dir, 0555);

        new FileFailingHostsCache(5, $cacheFile);
    }

    /**
     * @expectedException RuntimeException
     * @expectExceptionMessage is not readable
     */
    public function testShouldThrowAnExceptionIfCacheFileExistsButIsNotReadable()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cache_dir';
        $cacheFile = $dir . DIRECTORY_SEPARATOR . 'cache_file';
        @unlink($cacheFile);
        @rmdir($dir);
        mkdir($dir, 0777);
        touch($cacheFile);
        chmod($cacheFile, 0222); // not readable.

        new FileFailingHostsCache(5, $cacheFile);
    }

    /**
     * @expectedException RuntimeException
     * @expectExceptionMessage is not writable
     */
    public function testShouldThrowAnExceptionIfCacheFileExistsButIsNotWritable()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cache_dir';
        $cacheFile = $dir . DIRECTORY_SEPARATOR . 'cache_file';
        @unlink($cacheFile);
        @rmdir($dir);
        mkdir($dir, 0777);
        touch($cacheFile);
        chmod($cacheFile, 0555); // not writable.

        new FileFailingHostsCache(5, $cacheFile);
    }

    public function testShouldGracefullyHandleInvalidJsonInCacheFile()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cache_dir';
        $cacheFile = $dir . DIRECTORY_SEPARATOR . 'cache_file';
        @unlink($cacheFile);
        @rmdir($dir);
        mkdir($dir, 0777);
        file_put_contents($cacheFile, '{broken json');
        chmod($cacheFile, 0777); // not writable.

        $cache = new FileFailingHostsCache(5, $cacheFile);
        $hosts = $cache->getFailingHosts();
        $this->assertEmpty($hosts);
    }

    public function testThatDefaultTtlIs5Minutes()
    {
        $cache = new FileFailingHostsCache();
        $this->assertEquals(60*5, $cache->getTtl());
    }

    public function testThatTtlCanBeOverridden()
    {
        $cache = new FileFailingHostsCache(15);
        $this->assertEquals(15, $cache->getTtl());
    }

    /**
     * @param int $ttl
     *
     * @return FailingHostsCache
     */
    public function getNewCacheInstance($ttl = 2)
    {
        return new FileFailingHostsCache($ttl);
    }
}

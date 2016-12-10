<?php

namespace AlgoliaSearch\Tests;

require_once 'global_functions_stubs.php';

use AlgoliaSearch\FileFailingHostsCache;

class FileFailingHostsCacheTest extends FailingHostsCacheTestCase
{
    protected function tearDown()
    {
        global $make_is_writable_fail;
        $make_is_writable_fail = false;
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testShouldThrowAnExceptionIsCacheDirectoryIsNotWritable()
    {
        global $make_is_writable_fail;

        $make_is_writable_fail = true;
        new FileFailingHostsCache();
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
